<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Mcp;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Review\CommentNotFoundException;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Mcp\AddCommentReplyTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(AddCommentReplyTool::class)]
class AddCommentReplyToolTest extends AbstractTestCase
{
    private CommentRepository&MockObject      $commentRepository;
    private CommentReplyRepository&MockObject $commentReplyRepository;
    private Security&MockObject               $security;
    private MessageBusInterface&MockObject    $bus;
    private AddCommentReplyTool               $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository      = $this->createMock(CommentRepository::class);
        $this->commentReplyRepository = $this->createMock(CommentReplyRepository::class);
        $this->security               = $this->createMock(Security::class);
        $this->bus                    = $this->createMock(MessageBusInterface::class);
        $this->tool                   = new AddCommentReplyTool($this->commentRepository, $this->commentReplyRepository, $this->security, $this->bus);
    }

    public function testCommentNotFound(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->commentReplyRepository->expects($this->never())->method(static::anything());
        $this->security->expects($this->never())->method(static::anything());
        $this->bus->expects($this->never())->method(static::anything());

        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage('Comment not found: 123');
        ($this->tool)(123, 'Reply');
    }

    public function testDraftComment(): void
    {
        $comment = new Comment()->setType(CommentTypeEnum::Draft);
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->commentReplyRepository->expects($this->never())->method(static::anything());
        $this->security->expects($this->never())->method(static::anything());
        $this->bus->expects($this->never())->method(static::anything());

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Replies cannot be added to draft comments.');
        ($this->tool)(123, 'Reply');
    }

    #[DataProvider('invalidMessageProvider')]
    public function testInvalidMessage(string $message, string $error): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(new Comment());
        $this->commentReplyRepository->expects($this->never())->method(static::anything());
        $this->security->expects($this->never())->method(static::anything());
        $this->bus->expects($this->never())->method(static::anything());

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage($error);
        ($this->tool)(123, $message);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidMessageProvider(): array
    {
        return [
            'empty'      => ['', 'Reply message must not be blank.'],
            'whitespace' => [" \t\r\n ", 'Reply message must not be blank.'],
            'too long'   => [
                str_repeat('a', Comment::MAX_COMMENT_LENGTH + 1),
                'Reply message must not exceed ' . Comment::MAX_COMMENT_LENGTH . ' characters.'
            ]
        ];
    }

    #[DataProvider('validMessageProvider')]
    public function testAddReply(string $message, string $expectedMessage, CommentStateEnum $state): void
    {
        $comment = new Comment()->setId(123)->setReview(new CodeReview()->setId(456))->setFilePath('src/example.php')->setState($state);
        $user    = new User()->setId(789);

        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->commentReplyRepository->expects($this->once())->method('save')->with(
            static::callback(static function (CommentReply $reply) use ($comment, $user, $expectedMessage): bool {
                static::assertSame($comment, $reply->getComment());
                static::assertSame($user, $reply->getUser());
                static::assertSame($expectedMessage, $reply->getMessage());
                static::assertNull($reply->getTag());
                $reply->setId(321);

                return true;
            }),
            true
        );
        $this->bus->expects($this->once())->method('dispatch')
            ->with(new CommentReplyAdded(456, 321, 789, $expectedMessage, 'src/example.php'))
            ->willReturn($this->envelope);

        static::assertSame(321, ($this->tool)(123, $message));
        static::assertSame($state, $comment->getState());
    }

    /**
     * @return array<string, array{string, string, CommentStateEnum}>
     */
    public static function validMessageProvider(): array
    {
        $maxMessage = str_repeat('é', Comment::MAX_COMMENT_LENGTH);

        return [
            'markdown' => [
                " \t**Reply**\n\n```php\n    return true;\n```\r\n ",
                "**Reply**\n\n```php\n    return true;\n```",
                CommentStateEnum::Open
            ],
            'resolved' => ['Reply', 'Reply', CommentStateEnum::Resolved],
            'maximum length after trim' => [' ' . $maxMessage . ' ', $maxMessage, CommentStateEnum::Open]
        ];
    }
}
