<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\AddCommentController;
use DR\Review\Controller\App\Review\Comment\GetCommentThreadController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\AddCommentFormType;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractControllerTestCase<AddCommentController>
 */
#[CoversClass(AddCommentController::class)]
class AddCommentControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject $commentRepository;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        parent::setUp();
    }

    public function testInvokeFormNotSubmitted(): void
    {
        $this->commentRepository->expects($this->never())->method('save');
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);

        $this->expectGetUser(new User());
        $this->expectCreateForm(AddCommentFormType::class, static::isInstanceOf(Comment::class), ['review' => $review])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $response = ($this->controller)($request, $review);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testInvokeFormSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $user = new User();
        $this->expectGetUser($user);

        $this->expectCreateForm(AddCommentFormType::class, static::isInstanceOf(Comment::class), ['review' => $review])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->commentRepository->expects($this->once())
            ->method('save')
            ->with(
                self::callback(static function (Comment $comment) use ($user, $review) {
                    $comment->setId(123);
                    static::assertSame($user, $comment->getUser());
                    static::assertSame($review, $comment->getReview());
                    static::assertGreaterThan(0, $comment->getCreateTimestamp());
                    static::assertGreaterThan(0, $comment->getUpdateTimestamp());

                    return true;
                }),
                true
            );
        $this->expectGenerateUrl(GetCommentThreadController::class, ['id' => 123]);

        $response = ($this->controller)($request, $review);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new AddCommentController($this->commentRepository);
    }
}
