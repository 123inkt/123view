<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\DeleteCommentReplyController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $this->expectException(NotFoundHttpException::class);
        ($this->controller)(null);
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

        $response = ($this->controller)($reply);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new DeleteCommentReplyController($this->commentRepository);
    }
}
