<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\Comment\DeleteCommentReplyController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Security\Voter\CommentReplyVoter;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Comment\DeleteCommentReplyController
 * @covers ::__construct
 */
class DeleteCommentReplyControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject $commentRepository;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentReplyRepository::class);
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

        $reply = new CommentReply();
        $reply->setComment($comment);

        $this->expectDenyAccessUnlessGranted(CommentReplyVoter::DELETE, $reply);
        $this->commentRepository->expects(self::once())->method('remove')->with($reply, true);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        static::assertInstanceOf(RedirectResponse::class, ($this->controller)($reply));
    }

    public function getController(): AbstractController
    {
        return new DeleteCommentReplyController($this->commentRepository);
    }
}
