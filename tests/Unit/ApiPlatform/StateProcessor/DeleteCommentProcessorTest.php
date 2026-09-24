<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use DR\Review\ApiPlatform\StateProcessor\DeleteCommentProcessor;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Constraint\IsNull;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[CoversClass(DeleteCommentProcessor::class)]
class DeleteCommentProcessorTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private UserEntityProvider $userProvider;
    private CommentVisibility $commentVisibility;
    private AuthorizationCheckerInterface $authorizationChecker;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject $bus;
    private DeleteCommentProcessor $processor;
    private Comment $comment;
    private User $author;
    private User $currentUser;
    private bool $visible = true;
    private bool $authorized = true;
    private bool $removed = false;
    /** @var array{userLookups: int, visibilityChecks: int, authorizationChecks: int, preparedMessages: int} */
    private array $flowCounts = ['userLookups' => 0, 'visibilityChecks' => 0, 'authorizationChecks' => 0, 'preparedMessages' => 0];
    /** @var list<object> */
    private array $dispatchedMessages = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->userProvider = static::createStub(UserEntityProvider::class);
        $this->commentVisibility = static::createStub(CommentVisibility::class);
        $this->authorizationChecker = static::createStub(AuthorizationCheckerInterface::class);
        $this->messageFactory = $this->createMock(CommentEventMessageFactory::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->author = new User()->setId(10);
        $this->currentUser = $this->author;
        $this->dispatchedMessages = [];
        $this->flowCounts = ['userLookups' => 0, 'visibilityChecks' => 0, 'authorizationChecks' => 0, 'preparedMessages' => 0];
        $this->comment = new Comment()
            ->setId(123)
            ->setUser($this->author)
            ->setType(CommentTypeEnum::Final);

        $this->commentRepository
            ->method('find')
            ->willReturnCallback(fn(int|string $id): ?Comment => (int)$id === 123 ? $this->comment : null);
        $this->userProvider->method('getCurrentUser')->willReturnCallback(function (): User {
            $this->flowCounts['userLookups']++;

            return $this->currentUser;
        });
        $this->commentVisibility->method('isVisible')->willReturnCallback(function (): bool {
            $this->flowCounts['visibilityChecks']++;

            return $this->visible;
        });
        $this->authorizationChecker->method('isGranted')->willReturnCallback(function (): bool {
            $this->flowCounts['authorizationChecks']++;

            return $this->authorized;
        });

        $this->processor = new DeleteCommentProcessor(
            $this->commentRepository,
            $this->userProvider,
            $this->commentVisibility,
            $this->authorizationChecker,
            $this->messageFactory,
            $this->bus,
        );
    }

    public function testPreparesReplyMessagesBeforeDelete(): void
    {
        $firstReply = $this->addReply(1);
        $secondReply = $this->addReply(2);
        $firstMessage = new CommentReplyRemoved(1, 123, 1, 10, 10, 'first', null);
        $secondMessage = new CommentReplyRemoved(1, 123, 2, 10, 10, 'second', null);
        $messages = [$firstMessage, $secondMessage];

        $this->messageFactory
            ->expects($this->exactly(2))
            ->method('createReplyRemoved')
            ->willReturnCallback(function (CommentReply $reply, User $user) use ($firstReply, $secondReply, $messages): CommentReplyRemoved {
                self::assertFalse($this->removed, 'All reply messages must be prepared before the comment is removed.');
                self::assertSame($this->author, $user);
                self::assertContains($reply, [$firstReply, $secondReply]);
                $message = $messages[$this->flowCounts['preparedMessages']];
                $this->flowCounts['preparedMessages']++;

                return $message;
            });
        $this->commentRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->comment, true)
            ->willReturnCallback(function (): void {
                self::assertSame(2, $this->flowCounts['preparedMessages']);
                $this->removed = true;
            });
        $this->bus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (object $message): Envelope {
                self::assertTrue($this->removed, 'Reply messages must only be dispatched after the delete is flushed.');
                $this->dispatchedMessages[] = $message;

                return new Envelope($message);
            });

        $result = $this->processor->process(null, new Delete(), ['id' => '123']);
        self::assertThat($result, new IsNull());
        self::assertSame([$firstMessage, $secondMessage], $this->dispatchedMessages);
        self::assertSame(1, $this->flowCounts['userLookups']);
        self::assertSame(1, $this->flowCounts['visibilityChecks']);
        self::assertSame(1, $this->flowCounts['authorizationChecks']);
    }

    public function testDeletesAuthorsDraft(): void
    {
        $this->comment->setType(CommentTypeEnum::Draft);
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->commentRepository->expects($this->once())->method('remove')->with($this->comment, true);
        $this->bus->expects($this->never())->method('dispatch');

        $result = $this->processor->process(null, new Delete(), ['id' => '123']);
        self::assertThat($result, new IsNull());
        self::assertSame(1, $this->flowCounts['authorizationChecks']);
    }

    public function testMissingCommentReturnsNotFound(): void
    {
        $this->commentRepository->expects($this->never())->method('remove');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        try {
            $this->processor->process(null, new Delete(), ['id' => '456']);
            self::fail('Expected missing comment to return 404.');
        } catch (NotFoundHttpException) {
            self::assertSame(0, $this->flowCounts['userLookups']);
            self::assertSame(0, $this->flowCounts['authorizationChecks']);
        }
    }

    public function testForeignDraftReturnsNotFound(): void
    {
        $this->comment->setType(CommentTypeEnum::Draft);
        $this->currentUser = new User()->setId(20);
        $this->visible = false;
        $this->commentRepository->expects($this->never())->method('remove');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        try {
            $this->processor->process(null, new Delete(), ['id' => '123']);
            self::fail('Expected hidden draft to return 404.');
        } catch (NotFoundHttpException) {
            self::assertSame(0, $this->flowCounts['authorizationChecks']);
            self::assertSame(0, $this->flowCounts['preparedMessages']);
        }
    }

    public function testForeignFinalCommentReturnsForbidden(): void
    {
        $this->currentUser = new User()->setId(20);
        $this->authorized = false;
        $this->commentRepository->expects($this->never())->method('remove');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(AccessDeniedHttpException::class);
        $this->processor->process(null, new Delete(), ['id' => '123']);
    }

    public function testMessageFactoryFailureDoesNotDelete(): void
    {
        $this->addReply(1);
        $this->messageFactory
            ->expects($this->once())
            ->method('createReplyRemoved')
            ->willThrowException(new RuntimeException('Unable to create message.'));
        $this->commentRepository->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process(null, new Delete(), ['id' => '123']);
    }

    public function testFlushFailureDoesNotDispatch(): void
    {
        $this->addReply(1);
        $this->messageFactory
            ->expects($this->once())
            ->method('createReplyRemoved')
            ->willReturn(new CommentReplyRemoved(1, 123, 1, 10, 10, 'reply', null));
        $this->commentRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->comment, true)
            ->willThrowException(new RuntimeException('Unable to flush deletion.'));
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process(null, new Delete(), ['id' => '123']);
    }

    public function testRejectsNonDeleteOperation(): void
    {
        $this->commentRepository->expects($this->never())->method('find');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');
        $this->expectException(RuntimeException::class);

        $this->processor->process(null, new Patch(), ['id' => '123']);
    }

    private function addReply(int $id): CommentReply
    {
        $reply = new CommentReply();
        $reply->setId($id);
        $reply->setMessage('reply');
        $reply->setComment($this->comment);
        $reply->setUser($this->author);
        $this->comment->getReplies()->add($reply);

        return $reply;
    }
}
