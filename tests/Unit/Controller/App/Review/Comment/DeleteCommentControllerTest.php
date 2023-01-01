<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\DeleteCommentController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\DeleteCommentController
 * @covers ::__construct
 */
class DeleteCommentControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject          $commentRepository;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject        $bus;
    private Envelope                              $envelope;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->messageFactory    = $this->createMock(CommentEventMessageFactory::class);
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeCommentMissing(): void
    {
        $this->expectException(NotFoundHttpException::class);
        ($this->controller)(null);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $review = new CodeReview();
        $review->setId(456);
        $comment = new Comment();
        $comment->setId(123);
        $comment->setLineReference(new LineReference('file', 1, 2, 3));
        $comment->setReview($review);
        $event = new CommentRemoved(1, 2, 3, 'file', 'message');

        $user = new User();
        $this->expectGetUser($user);

        $this->expectDenyAccessUnlessGranted(CommentVoter::DELETE, $comment);
        $this->commentRepository->expects(self::once())->method('remove')->with($comment, true);
        $this->messageFactory->expects(self::once())->method('createRemoved')->willReturn($event);
        $this->bus->expects(self::once())->method('dispatch')->with($event)->willReturn($this->envelope);

        $response = ($this->controller)($comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new DeleteCommentController($this->commentRepository, $this->messageFactory, $this->bus);
    }
}
