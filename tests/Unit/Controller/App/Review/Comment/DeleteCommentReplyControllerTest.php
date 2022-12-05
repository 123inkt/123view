<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\DeleteCommentReplyController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\DeleteCommentReplyController
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
    public function testInvokeCommentMissing(): void
    {
        $this->expectRefererRedirect(ProjectsController::class);

        $response = ($this->controller)(null);
        static::assertInstanceOf(RedirectResponse::class, $response);
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
