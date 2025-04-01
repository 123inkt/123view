<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\DeleteCommentController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractControllerTestCase<DeleteCommentController>
 */
#[CoversClass(DeleteCommentController::class)]
class DeleteCommentControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject          $commentRepository;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject        $bus;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->messageFactory    = $this->createMock(CommentEventMessageFactory::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    public function testInvokeCommentMissing(): void
    {
        $this->expectException(NotFoundHttpException::class);
        ($this->controller)(null);
    }

    public function testInvoke(): void
    {
        $review = new CodeReview();
        $review->setId(456);
        $reply   = new CommentReply();
        $comment = new Comment();
        $comment->setId(123);
        $comment->setLineReference(new LineReference('file', 'file', 1, 2, 3));
        $comment->setReview($review);
        $comment->getReplies()->add($reply);
        $replyEvent = new CommentReplyRemoved(1, 2, 3, 4, 5, 'message', null);

        $user = new User();
        $this->expectGetUser($user);

        $this->expectDenyAccessUnlessGranted(CommentVoter::DELETE, $comment);
        $this->commentRepository->expects(self::once())->method('remove')->with($comment, true);
        $this->messageFactory->expects(self::once())->method('createReplyRemoved')->willReturn($replyEvent);
        $this->bus->expects(self::once())->method('dispatch')->with($replyEvent)->willReturn($this->envelope);

        $response = ($this->controller)($comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new DeleteCommentController($this->commentRepository, $this->messageFactory, $this->bus);
    }
}
