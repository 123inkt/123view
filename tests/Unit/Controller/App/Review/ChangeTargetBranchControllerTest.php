<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\App\Review\ChangeTargetBranchController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\ChangeReviewTargetBranchFormType;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @extends AbstractControllerTestCase<ChangeTargetBranchController>
 */
#[CoversClass(ChangeTargetBranchController::class)]
class ChangeTargetBranchControllerTest extends AbstractControllerTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;

    protected function setUp(): void
    {
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        parent::setUp();
    }

    public function testInvokeWithFailure(): void
    {
        $this->reviewRepository->expects($this->never())->method('save');
        $request = new Request();
        $review  = new CodeReview();

        $this->expectCreateForm(ChangeReviewTargetBranchFormType::class, $review, ['review' => $review])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid form submission');
        ($this->controller)($request, $review);
    }

    public function testInvokeWithSuccess(): void
    {
        $request = new Request();
        $review  = new CodeReview();

        $this->expectCreateForm(ChangeReviewTargetBranchFormType::class, $review, ['review' => $review])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);
        $this->reviewRepository->expects($this->once())
            ->method('save')
            ->with($review, true);
        $this->expectRedirectToRoute(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    public function getController(): AbstractController
    {
        return new ChangeTargetBranchController($this->reviewRepository);
    }
}
