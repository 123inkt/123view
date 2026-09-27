<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Patch;
use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\ApiPlatform\Input\UpdateCommentReplyInput;
use DR\Review\ApiPlatform\StateProcessor\UpdateCommentReplyProcessor;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[CoversClass(UpdateCommentReplyProcessor::class)]
class UpdateCommentReplyProcessorTest extends AbstractTestCase
{
    use ClockTestTrait;

    private CommentReplyRepository&MockObject        $commentReplyRepository;
    private UserEntityProvider&Stub                    $userProvider;
    private CommentVisibility&MockObject              $commentVisibility;
    private AuthorizationCheckerInterface&MockObject  $authorizationChecker;
    private MessageBusInterface&MockObject             $bus;
    private CommentReplyOutputFactory                  $outputFactory;
    private UpdateCommentReplyProcessor                $processor;
    private Comment                                    $comment;
    private CommentReply                               $reply;
    private User                                        $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->userProvider           = static::createStub(UserEntityProvider::class);
        $this->commentVisibility      = $this->createMock(CommentVisibility::class);
        $this->authorizationChecker   = $this->createMock(AuthorizationCheckerInterface::class);
        $this->bus                    = $this->createMock(MessageBusInterface::class);
        $this->outputFactory          = new CommentReplyOutputFactory();
        $this->processor              = new UpdateCommentReplyProcessor(
            $this->commentReplyRepository,
            $this->userProvider,
            $this->commentVisibility,
            $this->authorizationChecker,
            $this->bus,
            $this->outputFactory,
        );

        $this->user = new User()->setId(10);
        $this->comment = new Comment()
            ->setId(20)
            ->setReview(new CodeReview()->setId(30))
            ->setUser($this->user)
            ->setFilePath('src/Foo.php')
            ->setType(CommentTypeEnum::Final);
        $this->reply = new CommentReply()
            ->setId(40)
            ->setUser($this->user)
            ->setTag(CommentTagEnum::Suggestion);
        $this->reply->setComment($this->comment);
        $this->reply->setMessage('Original reply');
        $this->reply->setCreateTimestamp(1_000);
        $this->reply->setUpdateTimestamp(1_000);
    }

    public function testUpdatesAndDispatchesOriginal(): void
    {
        $this->configureAccessibleReply();
        $input          = new UpdateCommentReplyInput();
        $input->message = '  Updated reply  ';
        $input->setTag(CommentTagEnum::ChangeRequest);

        $this->commentReplyRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->reply, true);
        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with(new CommentReplyUpdated(30, 40, 10, 'Original reply'))
            ->willReturn($this->envelope);

        $output = $this->processor->process($input, new Patch(), ['id' => '40']);

        self::assertSame('Updated reply', $output->message);
        self::assertSame(CommentTagEnum::ChangeRequest->value, $output->tag);
        self::assertSame(self::time(), $output->updatedAt->getTimestamp());
    }

    public function testTagOnlyUpdateDoesNotDispatchEvent(): void
    {
        $this->configureAccessibleReply();
        $input = new UpdateCommentReplyInput();
        $input->setTag(null);

        $this->commentReplyRepository->expects($this->once())->method('save')->with($this->reply, true);
        $this->bus->expects($this->never())->method('dispatch');

        $output = $this->processor->process($input, new Patch(), ['id' => '40']);

        self::assertSame('Original reply', $output->message);
        self::assertNull($output->tag);
    }

    public function testUnchangedMessageDoesNotDispatchEvent(): void
    {
        $this->configureAccessibleReply();
        $input          = new UpdateCommentReplyInput();
        $input->message = ' Original reply ';

        $this->commentReplyRepository->expects($this->once())->method('save')->with($this->reply, true);
        $this->bus->expects($this->never())->method('dispatch');

        $this->processor->process($input, new Patch(), ['id' => '40']);

        self::assertSame('Original reply', $this->reply->getMessage());
    }

    public function testMissingReplyReturns404(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(40)->willReturn(null);
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->authorizationChecker->expects($this->never())->method('isGranted');
        $this->commentReplyRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->inputWithMessage('Updated'), new Patch(), ['id' => '40']);
    }

    public function testHiddenReplyIs404BeforeAuth(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(40)->willReturn($this->reply);
        $this->userProvider->method('getCurrentUser')->willReturn($this->user);
        $this->commentVisibility->expects($this->once())->method('isVisible')->with($this->comment, $this->user)->willReturn(false);
        $this->authorizationChecker->expects($this->never())->method('isGranted');
        $this->commentReplyRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->inputWithMessage('Updated'), new Patch(), ['id' => '40']);
    }

    public function testUnauthorizedReplyIsForbidden(): void
    {
        $this->configureAccessibleReply(false);
        $this->commentReplyRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(AccessDeniedHttpException::class);
        $this->processor->process($this->inputWithMessage('Unauthorized'), new Patch(), ['id' => '40']);
    }

    public function testPersistenceFailureSuppressesEvent(): void
    {
        $this->configureAccessibleReply();
        $this->commentReplyRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new RuntimeException('Unable to persist reply.'));
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process($this->inputWithMessage('Updated'), new Patch(), ['id' => '40']);
    }

    private function inputWithMessage(string $message): UpdateCommentReplyInput
    {
        $input          = new UpdateCommentReplyInput();
        $input->message = $message;

        return $input;
    }

    private function configureAccessibleReply(bool $authorized = true): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(40)->willReturn($this->reply);
        $this->userProvider->method('getCurrentUser')->willReturn($this->user);
        $this->commentVisibility->expects($this->once())->method('isVisible')->with($this->comment, $this->user)->willReturn(true);
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with(CommentReplyVoter::EDIT, $this->reply)
            ->willReturn($authorized);
    }

    protected function freezeTimeAt(): int
    {
        return 1_700_000_000;
    }
}
