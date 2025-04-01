<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\UpdateCommentController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Form\Review\EditCommentFormType;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<UpdateCommentController>
 */
#[CoversClass(UpdateCommentController::class)]
class UpdateCommentControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject   $commentRepository;
    private TranslatorInterface&MockObject $translator;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    public function testInvokeCommentMissing(): void
    {
        $response = ($this->controller)(new Request(), null);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testInvokeIsNotSubmitted(): void
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
            ->isSubmittedWillReturn(false);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

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

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        static::assertEqualsWithDelta(time(), $comment->getUpdateTimestamp(), 10);
    }

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

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        static::assertEqualsWithDelta(time(), $comment->getUpdateTimestamp(), 10);
    }

    public function getController(): AbstractController
    {
        return new UpdateCommentController($this->commentRepository, $this->translator);
    }
}
