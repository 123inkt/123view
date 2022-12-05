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
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Page\BreadcrumbFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\ReviewViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\ReviewController
 * @covers ::__construct
 */
class ReviewControllerTest extends AbstractControllerTestCase
{
    private ReviewViewModelProvider&MockObject $modelProvider;
    private BreadcrumbFactory&MockObject       $breadcrumbFactory;
    private FileSeenStatusService&MockObject   $fileSeenService;

    public function setUp(): void
    {
        $this->modelProvider     = $this->createMock(ReviewViewModelProvider::class);
        $this->breadcrumbFactory = $this->createMock(BreadcrumbFactory::class);
        $this->fileSeenService   = $this->createMock(FileSeenStatusService::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $action  = $this->createMock(AbstractReviewAction::class);
        $request = $this->createMock(ReviewRequest::class);
        $request->expects(self::once())->method('getFilePath')->willReturn('filepath');
        $request->expects(self::once())->method('getTab')->willReturn('tab');
        $request->expects(self::once())->method('getAction')->willReturn($action);

        $user = new User();
        $this->expectGetUser($user);

        $breadcrumb = new Breadcrumb('label', 'url');

        $repository = new Repository();
        $repository->setDisplayName('repository');
        $review = new CodeReview();
        $review->setRepository($repository);
        $review->setProjectId(123);

        $diffFile  = new DiffFile();
        $viewModel = new ReviewViewModel($review);
        $viewModel->setFileDiffViewModel(new FileDiffViewModel($diffFile));

        $this->modelProvider->expects(self::once())->method('getViewModel')->with($review, 'filepath', 'tab', $action)->willReturn($viewModel);
        $this->fileSeenService->expects(self::once())->method('markAsSeen')->with($review, $user, $diffFile);
        $this->breadcrumbFactory->expects(self::once())->method('createForReview')->with($review)->willReturn([$breadcrumb]);

        $data = ($this->controller)($request, $review);
        static::assertSame('CR-123 - Repository', $data['page_title']);
        static::assertSame([$breadcrumb], $data['breadcrumbs']);
        static::assertSame($viewModel, $data['reviewModel']);
    }

    /**
     * @covers ::redirectReviewRoute
     */
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
