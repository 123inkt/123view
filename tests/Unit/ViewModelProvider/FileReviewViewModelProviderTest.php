<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\CodeReviewTypeDecider;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use DR\Review\ViewModelProvider\FileReviewViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(FileReviewViewModelProvider::class)]
class FileReviewViewModelProviderTest extends AbstractTestCase
{
    private FileDiffViewModelProvider&MockObject $fileDiffViewModelProvider;
    private CodeReviewFileService&MockObject     $fileService;
    private CodeReviewTypeDecider&MockObject     $reviewTypeDecider;
    private CodeReviewRevisionService&MockObject $revisionService;
    private RevisionVisibilityService&MockObject $visibilityService;
    private FileReviewViewModelProvider          $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileDiffViewModelProvider = $this->createMock(FileDiffViewModelProvider::class);
        $this->fileService               = $this->createMock(CodeReviewFileService::class);
        $this->reviewTypeDecider         = $this->createMock(CodeReviewTypeDecider::class);
        $this->revisionService           = $this->createMock(CodeReviewRevisionService::class);
        $this->visibilityService         = $this->createMock(RevisionVisibilityService::class);
        $this->provider                  = new FileReviewViewModelProvider(
            $this->fileDiffViewModelProvider,
            $this->fileService,
            $this->reviewTypeDecider,
            $this->revisionService,
            $this->visibilityService
        );
    }

    /**
     * @throws Throwable
     */
    public function testGetViewModel(): void
    {
        $review    = new CodeReview();
        $revision  = new Revision();
        $file      = new DiffFile();
        $viewModel = $this->createMock(FileDiffViewModel::class);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->visibilityService->expects($this->once())->method('getVisibleRevisions')->with($review, [$revision])->willReturn([$revision]);
        $this->reviewTypeDecider->expects($this->once())->method('decide')
            ->with($review, [$revision], [$revision])
            ->willReturn(CodeReviewType::COMMITS);
        $this->fileService->expects($this->once())->method('getFiles')
            ->with(
                $review,
                [$revision],
                'filepath',
                new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, DiffComparePolicy::IGNORE, CodeReviewType::COMMITS, 6)
            )
            ->willReturn([[], $file]);
        $this->fileDiffViewModelProvider->expects($this->once())->method('getFileDiffViewModel')
            ->with($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE)
            ->willReturn($viewModel);

        $result = $this->provider->getViewModel($review, 'filepath', DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE, 6);
        static::assertSame($viewModel, $result);
    }
}
