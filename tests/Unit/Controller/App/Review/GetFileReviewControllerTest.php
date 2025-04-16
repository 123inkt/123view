<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\GetFileReviewController;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Request\Review\FileReviewRequest;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModelProvider\FileReviewViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<GetFileReviewController>
 */
#[CoversClass(GetFileReviewController::class)]
class GetFileReviewControllerTest extends AbstractControllerTestCase
{
    private FileReviewViewModelProvider&MockObject $modelProvider;
    private FileSeenStatusService&MockObject       $fileSeenService;

    protected function setUp(): void
    {
        $this->modelProvider   = $this->createMock(FileReviewViewModelProvider::class);
        $this->fileSeenService = $this->createMock(FileSeenStatusService::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request = $this->createMock(FileReviewRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getComparisonPolicy')->willReturn(DiffComparePolicy::IGNORE);
        $request->method('getDiffMode')->willReturn(ReviewDiffModeEnum::INLINE);

        $user      = new User();
        $review    = new CodeReview();
        $file      = new DiffFile();
        $viewModel = new FileDiffViewModel($file, ReviewDiffModeEnum::INLINE);

        $this->expectGetUser($user);
        $this->modelProvider->expects($this->once())->method('getViewModel')
            ->with($review, 'filepath', DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE)
            ->willReturn($viewModel);
        $this->fileSeenService->expects($this->once())->method('markAsSeen')->with($review, $user, $file);
        $this->expectRender('app/review/commit/commit.file.html.twig', ['fileDiffViewModel' => $viewModel]);

        ($this->controller)($request, $review);
    }

    public function testInvokeWithSideBySide(): void
    {
        $request = $this->createMock(FileReviewRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getComparisonPolicy')->willReturn(DiffComparePolicy::IGNORE);
        $request->method('getDiffMode')->willReturn(ReviewDiffModeEnum::SIDE_BY_SIDE);

        $user                 = new User();
        $review               = new CodeReview();
        $file                 = new DiffFile();
        $file->filePathBefore = 'before';
        $file->filePathAfter  = 'after';
        $viewModel            = new FileDiffViewModel($file, ReviewDiffModeEnum::SIDE_BY_SIDE);

        $this->expectGetUser($user);
        $this->modelProvider->expects($this->once())->method('getViewModel')
            ->with($review, 'filepath', DiffComparePolicy::IGNORE, ReviewDiffModeEnum::SIDE_BY_SIDE)
            ->willReturn($viewModel);
        $this->fileSeenService->expects($this->once())->method('markAsSeen')->with($review, $user, $file);
        $this->expectRender('app/review/commit/side-by-side/commit.file.html.twig', ['fileDiffViewModel' => $viewModel]);

        ($this->controller)($request, $review);
    }

    public function getController(): AbstractController
    {
        return new GetFileReviewController($this->modelProvider, $this->fileSeenService);
    }
}
