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
use Symfony\Component\Form\FormView;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\GetAddCommentFormController
 */
class GetAddCommentFormControllerTest extends AbstractControllerTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $lineReference = new LineReference('filepath', 1, 2, 3);
        $request       = $this->createMock(AddCommentRequest::class);
        $request->method('getLineReference')->willReturn($lineReference);
        $review = new CodeReview();
        $review->setId(123);

        $view = $this->createMock(FormView::class);

        $this->expectCreateForm(AddCommentFormType::class, null, ['review' => $review, 'lineReference' => $lineReference])
            ->createViewWillReturn($view);

        static::assertSame(['form' => $view], ($this->controller)($request, $review));
    }

    public function getController(): AbstractController
    {
        return new GetAddCommentFormController();
    }
}
