<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Post;
use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\ApiPlatform\Input\CreateCommentReplyInput;
use DR\Review\ApiPlatform\StateProcessor\CreateCommentReplyProcessor;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(CreateCommentReplyProcessor::class)]
class CreateCommentReplyProcessorTest extends AbstractTestCase
{
    use ClockTestTrait;

    private CommentRepository&MockObject $commentRepository;
    private CommentReplyRepository&MockObject $commentReplyRepository;
    private UserEntityProvider $userProvider;
    private MessageBusInterface&MockObject $bus;
    private CommentReplyOutputFactory $outputFactory;
    private CreateCommentReplyProcessor $processor;
    private Comment $comment;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepository      = $this->createMock(CommentRepository::class);
        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->userProvider           = static::createStub(UserEntityProvider::class);
        $this->bus                    = $this->createMock(MessageBusInterface::class);
        $this->outputFactory          = new CommentReplyOutputFactory();
        $this->processor              = new CreateCommentReplyProcessor(
            $this->commentRepository,
            $this->commentReplyRepository,
            $this->userProvider,
            $this->bus,
            $this->outputFactory,
        );

        $this->user    = new User()->setId(10);
        $this->comment = new Comment()
            ->setId(20)
            ->setReview(new CodeReview()->setId(30))
            ->setUser($this->user)
            ->setFilePath('src/Foo.php')
            ->setType(CommentTypeEnum::Final);
        $this->userProvider->method('getCurrentUser')->willReturn($this->user);
    }

    public function testCreatesReplyAndDispatchesEvent(): void
    {
        $input       = $this->input('  Please extract this condition.  ', CommentTagEnum::Suggestion);

        $this->commentRepository->expects($this->once())->method('find')->with(20)->willReturn($this->comment);
        $this->commentReplyRepository
            ->expects($this->once())
            ->method('save')
            ->with(self::isInstanceOf(CommentReply::class), true)
            ->willReturnCallback(static function (CommentReply $reply): void {
                $reply->setId(40);
            });
        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with(new CommentReplyAdded(30, 40, 10, 'Please extract this condition.', 'src/Foo.php'))
            ->willReturn($this->envelope);

        $output = $this->processor->process($input, new Post(), ['commentId' => '20']);
        self::assertSame(40, $output->id);
        self::assertSame(20, $output->commentId);
        self::assertSame(10, $output->userId);
        self::assertSame('Please extract this condition.', $output->message);
        self::assertSame(CommentTagEnum::Suggestion->value, $output->tag);
        self::assertSame(self::time(), $output->createdAt->getTimestamp());
        self::assertSame(self::time(), $output->updatedAt->getTimestamp());
    }

    public function testRejectsMissingComment(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(20)->willReturn(null);
        $this->commentReplyRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->input('Reply', null), new Post(), ['commentId' => '20']);
    }

    public function testRejectsDraftParent(): void
    {
        $this->comment->setType(CommentTypeEnum::Draft);
        $this->commentRepository->expects($this->once())->method('find')->with(20)->willReturn($this->comment);
        $this->commentReplyRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(BadRequestHttpException::class);
        $this->processor->process($this->input('Reply', null), new Post(), ['commentId' => '20']);
    }

    public function testDoesNotDispatchWhenPersistenceFails(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->willReturn($this->comment);
        $this->commentReplyRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new RuntimeException('Unable to persist reply.'));
        $this->bus->expects($this->never())->method('dispatch');

        $this->expectException(RuntimeException::class);
        $this->processor->process($this->input('Reply', null), new Post(), ['commentId' => '20']);
    }

    private function input(string $message, ?CommentTagEnum $tag): CreateCommentReplyInput
    {
        $input          = new CreateCommentReplyInput();
        $input->message = $message;
        $input->tag     = $tag;

        return $input;
    }

    protected function freezeTimeAt(): int
    {
        return 1_700_000_000;
    }
}
