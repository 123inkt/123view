<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\AddCommentReactionController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(AddCommentReactionController::class)]
class AddCommentReactionControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject $commentRepository;
    private MessageBusInterface&MockObject    $bus;
    private Envelope                          $envelope;

    protected function setUp(): void
    {
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->commentRepository = $this->createMock(CommentReplyRepository::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('message');

        $user    = new User();
        $comment = new Comment();

        $this->expectGetUser($user);
        $this->commentRepository
            ->expects(self::once())
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
        $this->bus->expects(self::once())->method('dispatch')->with(self::isInstanceOf(CommentReplyAdded::class))->willReturn($this->envelope);

        ($this->controller)($request, $comment);
    }

    public function getController(): AbstractController
    {
        return new AddCommentReactionController($this->commentRepository, $this->bus);
    }
}
