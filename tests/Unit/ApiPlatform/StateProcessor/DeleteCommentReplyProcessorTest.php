<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use DR\Review\ApiPlatform\StateProcessor\DeleteCommentReplyProcessor;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[CoversClass(DeleteCommentReplyProcessor::class)]
class DeleteCommentReplyProcessorTest extends AbstractTestCase
{
    private CommentReplyRepository&MockObject       $commentReplyRepository;
    private UserEntityProvider                      $userProvider;
    private CommentVisibility&MockObject            $commentVisibility;
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private CommentEventMessageFactory&MockObject   $messageFactory;
    private MessageBusInterface&MockObject          $bus;
    private DeleteCommentReplyProcessor              $processor;
    private Comment                                  $comment;
    private CommentReply                             $reply;
    private User                                     $user;
    private bool                                     $eventPrepared = false;
    private bool                                     $removed       = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->userProvider           = static::createStub(UserEntityProvider::class);
        $this->commentVisibility      = $this->createMock(CommentVisibility::class);
        $this->authorizationChecker   = $this->createMock(AuthorizationCheckerInterface::class);
        $this->messageFactory         = $this->createMock(CommentEventMessageFactory::class);
        $this->bus                    = $this->createMock(MessageBusInterface::class);
        $this->processor              = new DeleteCommentReplyProcessor(
            $this->commentReplyRepository,
            $this->userProvider,
            $this->commentVisibility,
            $this->authorizationChecker,
            $this->messageFactory,
            $this->bus,
        );

        $this->user = new User()->setId(10);
        $this->comment = new Comment()
            ->setId(20)
            ->setReview(new CodeReview()->setId(30))
            ->setUser($this->user);
        $this->reply = new CommentReply();
        $this->reply->setId(40);
        $this->reply->setComment($this->comment);
        $this->reply->setUser($this->user)->setExtReferenceId('external-40');
        $this->reply->setMessage('Original reply');
        $this->userProvider->method('getCurrentUser')->willReturn($this->user);
    }

    public function testDeletesReplyDispatchesAfterFlush(): void
    {
        $event = new CommentReplyRemoved(30, 20, 40, 10, 10, 'Original reply', 'external-40');
        $this->configureAccessibleReply();
        $this->messageFactory
            ->expects($this->once())
            ->method('createReplyRemoved')
            ->with($this->reply, $this->user)
            ->willReturnCallback(function () use ($event): CommentReplyRemoved {
                $this->eventPrepared = true;

                return $event;
            });
        $this->commentReplyRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->reply, true)
            ->willReturnCallback(function (): void {
                self::assertTrue($this->eventPrepared);
                $this->removed = true;
            });
        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturnCallback(function (object $message): Envelope {
                self::assertTrue($this->removed);

                return new Envelope($message);
            });

        $this->processor->process($this->reply, new Delete(), ['id' => '40']);
    }

    public function testMissingReplyReturns404(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(40)->willReturn(null);
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->authorizationChecker->expects($this->never())->method('isGranted');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->commentReplyRepository->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->reply, new Delete(), ['id' => '40']);
    }

    public function testHiddenReply404BeforeAuthorization(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(40)->willReturn($this->reply);
        $this->commentVisibility->expects($this->once())->method('isVisible')->with($this->comment, $this->user)->willReturn(false);
        $this->authorizationChecker->expects($this->never())->method('isGranted');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->commentReplyRepository->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->reply, new Delete(), ['id' => '40']);
    }

    public function testOtherAuthorForbiddenNoMutation(): void
    {
        $this->configureAccessibleReply(false);
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->commentReplyRepository->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(AccessDeniedHttpException::class);
        $this->processor->process($this->reply, new Delete(), ['id' => '40']);
    }

    public function testMessageFactoryFailureDoesNotDelete(): void
    {
        $this->configureAccessibleReply();
        $this->messageFactory
            ->expects($this->once())
            ->method('createReplyRemoved')
            ->willThrowException(new RuntimeException('Unable to create removal event.'));
        $this->commentReplyRepository->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process($this->reply, new Delete(), ['id' => '40']);
    }

    public function testFlushFailureDoesNotDispatch(): void
    {
        $this->configureAccessibleReply();
        $this->messageFactory
            ->expects($this->once())
            ->method('createReplyRemoved')
            ->willReturn(new CommentReplyRemoved(30, 20, 40, 10, 10, 'Original reply', 'external-40'));
        $this->commentReplyRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->reply, true)
            ->willThrowException(new RuntimeException('Unable to flush deletion.'));
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process($this->reply, new Delete(), ['id' => '40']);
    }

    public function testRejectsNonDeleteOperation(): void
    {
        $this->commentReplyRepository->expects($this->never())->method('find');
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->authorizationChecker->expects($this->never())->method('isGranted');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->commentReplyRepository->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process($this->reply, new Patch());
    }

    private function configureAccessibleReply(bool $authorized = true): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(40)->willReturn($this->reply);
        $this->commentVisibility->expects($this->once())->method('isVisible')->with($this->comment, $this->user)->willReturn(true);
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with(CommentReplyVoter::DELETE, $this->reply)
            ->willReturn($authorized);
    }
}
