<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\Comment\DeleteCommentController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Security\Voter\CommentVoter;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Comment\DeleteCommentController
 * @covers ::__construct
 */
class DeleteCommentControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject $commentRepository;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $comment = new Comment();
        $comment->setId(123);
        $review = new CodeReview();
        $review->setId(456);
        $comment->setReview($review);

        $this->expectedDenyAccessUnlessGranted(CommentVoter::DELETE, $comment);
        $this->commentRepository->expects(self::once())->method('remove')->with($comment, true);
        $this->expectRefererRedirect(ReviewController::class, ['id' => 456]);

        static::assertInstanceOf(RedirectResponse::class, ($this->controller)($comment));
    }

    public function getController(): AbstractController
    {
        return new DeleteCommentController($this->commentRepository);
    }
}
