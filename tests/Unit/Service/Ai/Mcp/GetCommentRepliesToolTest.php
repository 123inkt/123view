<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Ai\Mcp\GetCommentRepliesTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GetCommentRepliesTool::class)]
class GetCommentRepliesToolTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private GetCommentRepliesTool         $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->tool              = new GetCommentRepliesTool($this->commentRepository);
    }

    public function testInvokeThrowsWhenCommentNotFound(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(null);

        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage('Comment not found: 123');
        ($this->tool)(123);
    }

    public function testInvokeReturnsNoReplies(): void
    {
        $comment = new Comment()->setId(123);
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);

        static::assertSame([], ($this->tool)(123));
    }

    public function testInvokeReturnsMappedReplies(): void
    {
        $comment = new Comment()->setId(123);
        $user    = new User()->setId(7)->setName('Jane Doe')->setEmail('jane@example.com');
        $reply   = new CommentReply()->setId(456)->setUser($user);
        $reply->setComment($comment);
        $reply->setMessage('Looks good');
        $reply->setCreateTimestamp(1700000000);
        $comment->getReplies()->add($reply);

        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);

        $result = ($this->tool)(123);

        static::assertSame(
            [
                'replyId'   => 456,
                'commentId' => 123,
                'message'   => 'Looks good',
                'author'    => [
                    'userId' => 7,
                    'name'   => 'Jane Doe',
                    'email'  => 'jane@example.com',
                ],
                'createdAt' => date('c', 1700000000),
            ],
            $result[0]
        );
    }
}
