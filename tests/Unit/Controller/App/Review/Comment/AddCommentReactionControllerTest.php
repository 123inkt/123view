<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\AddCommentReactionController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractControllerTestCase<AddCommentReactionController>
 */
#[CoversClass(AddCommentReactionController::class)]
class AddCommentReactionControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject $commentRepository;
    private MessageBusInterface&MockObject    $bus;

    protected function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentReplyRepository::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('message');

        $user    = (new User())->setId(123);
        $comment = (new Comment())->setFilePath('file');
        $comment->setReview(new CodeReview());

        $this->expectGetUser($user);
        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                self::callback(
                    static function (CommentReply $reply) use ($user, $comment): bool {
                        static::assertSame($user, $reply->getUser());
                        static::assertSame($comment, $reply->getComment());
                        static::assertSame('message', $reply->getMessage());

                        return true;
                    }
                )
            );
        $this->bus->expects($this->once())->method('dispatch')->with(self::isInstanceOf(CommentReplyAdded::class))->willReturn($this->envelope);

        ($this->controller)($request, $comment);
    }

    public function getController(): AbstractController
    {
        return new AddCommentReactionController($this->commentRepository, $this->bus);
    }
}
