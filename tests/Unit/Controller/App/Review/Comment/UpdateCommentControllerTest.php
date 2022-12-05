<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\UpdateCommentController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\EditCommentFormType;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\UpdateCommentController
 * @covers ::__construct
 */
class UpdateCommentControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject   $commentRepository;
    private MessageBusInterface&MockObject $bus;
    private Envelope                       $envelope;

    public function setUp(): void
    {
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeCommentMissing(): void
    {
        $this->expectAddFlash('warning', 'comment.was.deleted.meanwhile');
        $this->expectRefererRedirect(ProjectsController::class);

        $response = ($this->controller)(new Request(), null);
        static::assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeIsNotSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setReview($review);

        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $comment);
    }

    /**
     * @covers ::__invoke
     * @covers ::refererRedirect
     */
    public function testInvokeIsSubmittedWithoutChanges(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setMessage('message');
        $comment->setReview($review);

        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);
        $this->bus->expects(self::never())->method('dispatch');

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $comment);

        static::assertEqualsWithDelta(time(), $comment->getUpdateTimestamp(), 10);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeIsSubmittedWithChanges(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setMessage('message');
        $comment->setReview($review);

        $this->expectGetUser((new User())->setId(789));
        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->commentRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    static function (Comment $comment) {
                        $comment->setMessage('changed-message');

                        return true;
                    }
                ),
                true
            );
        $this->bus->expects(self::once())->method('dispatch')->with(new CommentUpdated(123, 456, 789, 'message'))->willReturn($this->envelope);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $comment);

        static::assertEqualsWithDelta(time(), $comment->getUpdateTimestamp(), 10);
    }

    public function getController(): AbstractController
    {
        return new UpdateCommentController($this->commentRepository, $this->bus);
    }
}
