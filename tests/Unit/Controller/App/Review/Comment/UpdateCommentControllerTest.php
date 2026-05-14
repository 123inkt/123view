<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\UpdateCommentController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Form\Review\EditCommentFormType;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Form\SubmitButton;
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
    private TranslatorInterface&Stub $translator;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->translator        = static::createStub(TranslatorInterface::class);
        parent::setUp();
    }

    public function testInvokeCommentMissing(): void
    {
        $this->commentRepository->expects($this->never())->method('save');
        $response = ($this->controller)(new Request(), null);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testInvokeIsNotSubmitted(): void
    {
        $this->commentRepository->expects($this->never())->method('save');
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
        $comment->setId(456);
        $comment->setMessage('message');
        $comment->setReview($review);

        /** @var SubmitButton&Stub $publishButton */
        $publishButton = static::createStub(SubmitButton::class);
        $publishButton->method('isClicked')->willReturn(false);

        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $formAssertion = $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);
        $formAssertion->form->method('get')->with('publish')->willReturn($publishButton);

        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);

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

        /** @var SubmitButton&Stub $publishButton */
        $publishButton = static::createStub(SubmitButton::class);
        $publishButton->method('isClicked')->willReturn(false);

        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $formAssertion = $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);
        $formAssertion->form->method('get')->with('publish')->willReturn($publishButton);

        $this->commentRepository
            ->expects($this->once())
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

    public function testInvokePublishDraftComment(): void
    {
        $request = new Request(query: ['mode' => 'final']);
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setMessage('message');
        $comment->setType(CommentTypeEnum::Draft);
        $comment->setReview($review);

        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
        static::assertSame(CommentTypeEnum::Final, $comment->getType());
    }

    public function getController(): AbstractController
    {
        return new UpdateCommentController($this->commentRepository, $this->translator);
    }
}
