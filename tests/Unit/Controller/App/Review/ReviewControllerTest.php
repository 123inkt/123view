<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Model\Page\Breadcrumb;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\ReviewViewModelProviderService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractControllerTestCase<ReviewController>
 */
#[CoversClass(ReviewController::class)]
class ReviewControllerTest extends AbstractControllerTestCase
{
    private ReviewViewModelProviderService&MockObject $modelProvider;
    private BreadcrumbFactory&MockObject       $breadcrumbFactory;
    private FileSeenStatusService&MockObject   $fileSeenService;

    public function setUp(): void
    {
        $this->modelProvider     = $this->createMock(ReviewViewModelProviderService::class);
        $this->breadcrumbFactory = $this->createMock(BreadcrumbFactory::class);
        $this->fileSeenService   = $this->createMock(FileSeenStatusService::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request = $this->createMock(ReviewRequest::class);

        $user = new User();
        $this->expectGetUser($user);

        $breadcrumb = new Breadcrumb('label', 'url');

        $repository = new Repository();
        $repository->setDisplayName('repository');
        $review = new CodeReview();
        $review->setRepository($repository);
        $review->setProjectId(123);

        $diffFile  = new DiffFile();
        $viewModel = new ReviewViewModel($review, []);
        $viewModel->setFileDiffViewModel(new FileDiffViewModel($diffFile, ReviewDiffModeEnum::INLINE));

        $this->modelProvider->expects($this->once())->method('getViewModel')->with($review, $request)->willReturn($viewModel);
        $this->fileSeenService->expects($this->once())->method('markAsSeen')->with($review, $user, $diffFile);
        $this->breadcrumbFactory->expects($this->once())->method('createForReview')->with($review)->willReturn([$breadcrumb]);

        $data = ($this->controller)($request, $review);
        static::assertSame('CR-123 - Repository', $data['page_title']);
        static::assertSame([$breadcrumb], $data['breadcrumbs']);
        static::assertSame($viewModel, $data['reviewModel']);
    }

    public function testRedirectReviewRoute(): void
    {
        $request = new Request(['foo' => 'bar']);
        $review  = new CodeReview();

        $this->expectRedirectToRoute(ReviewController::class, ['review' => $review, 'foo' => 'bar'])->willReturn('url');

        /** @var ReviewController $controller */
        $controller = $this->controller;
        $controller->redirectReviewRoute($request, $review);
    }

    public function getController(): AbstractController
    {
        return new ReviewController($this->modelProvider, $this->breadcrumbFactory, $this->fileSeenService);
    }
}
