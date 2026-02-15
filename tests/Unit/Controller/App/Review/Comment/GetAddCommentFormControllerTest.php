<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\GetAddCommentFormController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Form\Review\AddCommentFormType;
use DR\Review\Request\Comment\AddCommentRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\FormView;

/**
 * @extends AbstractControllerTestCase<GetAddCommentFormController>
 */
#[CoversClass(GetAddCommentFormController::class)]
class GetAddCommentFormControllerTest extends AbstractControllerTestCase
{
    public function testInvoke(): void
    {
        $lineReference = new LineReference('filepath', 'filepath', 1, 2, 3);
        $request       = static::createStub(AddCommentRequest::class);
        $request->method('getLineReference')->willReturn($lineReference);
        $review = new CodeReview();
        $review->setActors([1, 2, 3]);
        $review->setId(123);

        $view = static::createStub(FormView::class);

        $this->expectCreateForm(AddCommentFormType::class, null, ['review' => $review, 'lineReference' => $lineReference])
            ->createViewWillReturn($view);

        static::assertSame(['form' => $view, 'actors' => [1, 2, 3]], ($this->controller)($request, $review));
    }

    public function getController(): AbstractController
    {
        return new GetAddCommentFormController();
    }
}
