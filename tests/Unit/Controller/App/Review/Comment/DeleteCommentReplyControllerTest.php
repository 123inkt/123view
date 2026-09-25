<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\DeleteCommentReplyController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractControllerTestCase<DeleteCommentReplyController>
 */
#[CoversClass(DeleteCommentReplyController::class)]
class DeleteCommentReplyControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject     $commentRepository;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject        $bus;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentReplyRepository::class);
        $this->messageFactory    = $this->createMock(CommentEventMessageFactory::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    public function testInvokeCommentMissing(): void
    {
        $this->commentRepository->expects($this->never())->method('remove');
        $this->messageFactory->expects($this->never())->method('createReplyRemoved');
        $this->bus->expects($this->never())->method('dispatch');
        $this->expectException(NotFoundHttpException::class);
        ($this->controller)(null);
    }

    public function testInvoke(): void
    {
        $user    = new User();
        $comment = new Comment();
        $comment->setId(123);
        $review = new CodeReview();
        $review->setId(456);
        $comment->setReview($review);

        $reply = new CommentReply();
        $reply->setComment($comment);

        $event = new CommentReplyRemoved(1, 2, 3, 4, 5, 'message', null);

        $this->expectGetUser($user);
        $this->expectDenyAccessUnlessGranted(CommentReplyVoter::DELETE, $reply);
        $this->messageFactory->expects($this->once())->method('createReplyRemoved')->with($reply, $user)->willReturn($event);
        $this->commentRepository->expects($this->once())->method('remove')->with($reply, true);
        $this->bus->expects($this->once())->method('dispatch')->with($event)->willReturn(new Envelope(new stdClass()));

        $response = ($this->controller)($reply);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new DeleteCommentReplyController($this->commentRepository, $this->messageFactory, $this->bus);
    }
}
