<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\ChangeCommentStateController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Request\Comment\ChangeCommentStateRequest;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\ChangeCommentStateController
 * @covers ::__construct
 */
class ChangeCommentStateControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject          $commentRepository;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject        $bus;
    private Envelope                              $envelope;

    public function setUp(): void
    {
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->messageFactory    = $this->createMock(CommentEventMessageFactory::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeCommentMissing(): void
    {
        $request = $this->createMock(ChangeCommentStateRequest::class);

        $this->expectAddFlash('warning', 'comment.was.deleted.meanwhile');
        $this->expectRefererRedirect(ProjectsController::class);

        $response = ($this->controller)($request, null);
        static::assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @covers ::__invoke
     * @covers ::getUser
     */
    public function testInvoke(): void
    {
        $request = $this->createMock(ChangeCommentStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CommentStateType::RESOLVED);

        $review = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setState(CommentStateType::OPEN);
        $comment->setReview($review);

        $user = new User();
        $user->setId(789);

        $event = new CommentResolved(123, 456, 789, 'file');

        $this->expectGetUser($user);
        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);
        $this->messageFactory->expects(self::once())->method('createResolved')->willReturn($event);
        $this->bus->expects(self::once())->method('dispatch')->with($event)->willReturn($this->envelope);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $comment);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeShouldNotDispatchIfStateDidNotChange(): void
    {
        $request = $this->createMock(ChangeCommentStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CommentStateType::OPEN);

        $review = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setState(CommentStateType::OPEN);
        $comment->setReview($review);

        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);
        $this->messageFactory->expects(self::never())->method('createResolved');
        $this->bus->expects(self::never())->method('dispatch');
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $comment);
    }

    public function getController(): AbstractController
    {
        return new ChangeCommentStateController($this->commentRepository, $this->messageFactory, $this->bus);
    }
}
