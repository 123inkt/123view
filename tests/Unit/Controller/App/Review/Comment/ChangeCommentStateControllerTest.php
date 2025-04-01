<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\ChangeCommentStateController;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Request\Comment\ChangeCommentStateRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<ChangeCommentStateController>
 */
#[CoversClass(ChangeCommentStateController::class)]
class ChangeCommentStateControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject   $commentRepository;
    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    public function testInvokeCommentMissing(): void
    {
        $request = $this->createMock(ChangeCommentStateRequest::class);

        $response = ($this->controller)($request, null);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

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

        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testInvokeWithUnresolvedComment(): void
    {
        $request = $this->createMock(ChangeCommentStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CommentStateType::OPEN);

        $review = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setState(CommentStateType::RESOLVED);
        $comment->setReview($review);

        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new ChangeCommentStateController($this->commentRepository, $this->translator);
    }
}
