<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\Comment\UpdateCommentController;
use DR\GitCommitNotification\Controller\App\Review\ProjectsController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Form\Review\EditCommentFormType;
use DR\GitCommitNotification\Message\Comment\CommentUpdated;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Security\Voter\CommentVoter;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Comment\UpdateCommentController
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
        $this->bus->expects(self::once())->method('dispatch')->with(new CommentUpdated(123, 456, 'message'))->willReturn($this->envelope);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $comment);

        static::assertEqualsWithDelta(time(), $comment->getUpdateTimestamp(), 10);
    }

    public function getController(): AbstractController
    {
        return new UpdateCommentController($this->commentRepository, $this->bus);
    }
}
