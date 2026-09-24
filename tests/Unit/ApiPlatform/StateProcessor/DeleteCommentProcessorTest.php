<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use DR\Review\ApiPlatform\StateProcessor\DeleteCommentProcessor;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Constraint\IsNull;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(DeleteCommentProcessor::class)]
class DeleteCommentProcessorTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private UserEntityProvider $userProvider;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject $bus;
    private DeleteCommentProcessor $processor;
    private Comment $comment;
    private User $author;
    private bool $removed = false;
    private int $preparedMessageCount = 0;
    /** @var list<object> */
    private array $dispatchedMessages = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->userProvider = static::createStub(UserEntityProvider::class);
        $this->messageFactory = $this->createMock(CommentEventMessageFactory::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->author = new User()->setId(10);
        $this->comment = new Comment()
            ->setId(123)
            ->setReview(new CodeReview()->setId(1))
            ->setUser($this->author)
            ->setType(CommentTypeEnum::Final);
        $this->removed = false;
        $this->preparedMessageCount = 0;
        $this->dispatchedMessages = [];
        $this->userProvider->method('getCurrentUser')->willReturn($this->author);
        $this->commentRepository->expects($this->never())->method('find');

        $this->processor = new DeleteCommentProcessor(
            $this->commentRepository,
            $this->userProvider,
            $this->messageFactory,
            $this->bus,
        );
    }

    public function testPreparesReplyMessagesBeforeDelete(): void
    {
        $firstReply = $this->addReply(1, 'first');
        $secondReply = $this->addReply(2, 'second');
        $messages = [
            new CommentReplyRemoved(1, 123, 1, 10, 10, 'first', null),
            new CommentReplyRemoved(1, 123, 2, 10, 10, 'second', null),
        ];

        $this->messageFactory
            ->expects($this->exactly(2))
            ->method('createReplyRemoved')
            ->willReturnCallback(function (CommentReply $reply, User $user) use ($firstReply, $secondReply, $messages): CommentReplyRemoved {
                self::assertFalse($this->removed);
                self::assertSame($this->author, $user);
                self::assertContains($reply, [$firstReply, $secondReply]);
                $message = $messages[$this->preparedMessageCount];
                $this->preparedMessageCount++;

                return $message;
            });
        $this->commentRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->comment, true)
            ->willReturnCallback(function (): void {
                self::assertSame(2, $this->preparedMessageCount);
                $this->removed = true;
            });
        $this->bus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (object $message): Envelope {
                self::assertTrue($this->removed);
                $this->dispatchedMessages[] = $message;

                return new Envelope($message);
            });

        $result = $this->processor->process($this->comment, new Delete());

        self::assertThat($result, new IsNull());
        self::assertSame($messages, $this->dispatchedMessages);
    }

    public function testDeletesDraftComment(): void
    {
        $this->comment->setType(CommentTypeEnum::Draft);
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->commentRepository->expects($this->once())->method('remove')->with($this->comment, true);
        $this->bus->expects($this->never())->method('dispatch');

        $result = $this->processor->process($this->comment, new Delete());

        self::assertThat($result, new IsNull());
    }

    public function testMessageFactoryFailureDoesNotDelete(): void
    {
        $this->addReply(1, 'reply');
        $this->messageFactory
            ->expects($this->once())
            ->method('createReplyRemoved')
            ->willThrowException(new RuntimeException('Unable to create message.'));
        $this->commentRepository->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process($this->comment, new Delete());
    }

    public function testFlushFailureDoesNotDispatch(): void
    {
        $this->addReply(1, 'reply');
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
        $this->processor->process($this->comment, new Delete());
    }

    public function testRejectsNonDeleteOperation(): void
    {
        $this->commentRepository->expects($this->never())->method('remove');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process($this->comment, new Patch());
    }

    public function testRejectsMissingCommentData(): void
    {
        $this->commentRepository->expects($this->never())->method('remove');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process(null, new Delete());
    }

    private function addReply(int $id, string $message): CommentReply
    {
        $reply = new CommentReply();
        $reply->setId($id);
        $reply->setMessage($message);
        $reply->setComment($this->comment);
        $reply->setUser($this->author);
        $this->comment->getReplies()->add($reply);

        return $reply;
    }
}
