<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\CodeReviewDtoProvider;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\UserReviewSettingsProvider;
use DR\Review\Service\Git\Review\CodeReviewTypeDecider;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

#[CoversClass(CodeReviewDtoProvider::class)]
class CodeReviewDtoProviderTest extends AbstractTestCase
{
    private CodeReviewRevisionService&MockObject  $revisionService;
    private CodeReviewFileService&MockObject      $fileService;
    private CodeReviewTypeDecider&MockObject      $reviewTypeDecider;
    private RevisionVisibilityService&MockObject  $visibilityService;
    private CodeReviewRepository&MockObject       $codeReviewRepository;
    private UserReviewSettingsProvider&MockObject $settingsProvider;
    private CodeReviewDtoProvider                 $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revisionService      = $this->createMock(CodeReviewRevisionService::class);
        $this->fileService          = $this->createMock(CodeReviewFileService::class);
        $this->reviewTypeDecider    = $this->createMock(CodeReviewTypeDecider::class);
        $this->visibilityService    = $this->createMock(RevisionVisibilityService::class);
        $this->codeReviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->settingsProvider     = $this->createMock(UserReviewSettingsProvider::class);
        $this->provider             = new CodeReviewDtoProvider(
            $this->revisionService,
            $this->fileService,
            $this->reviewTypeDecider,
            $this->visibilityService,
            $this->codeReviewRepository,
            $this->settingsProvider
        );
    }

    public function testProvide(): void
    {
        $request        = $this->createRequest();
        $review         = static::createStub(CodeReview::class);
        $revision1      = new Revision();
        $revision2      = new Revision();
        $fileTree       = new DirectoryTreeNode('name');
        $selectedFile   = new DiffFile();
        $similarReviews = [new CodeReview(), new CodeReview()];

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision1]);
        $this->visibilityService->expects($this->once())
            ->method('getVisibleRevisions')
            ->with($review, [$revision1])
            ->willReturn([$revision2]);
        $this->codeReviewRepository->expects($this->once())
            ->method('findByTitle')
            ->with($review)
            ->willReturn($similarReviews);
        $this->reviewTypeDecider->expects($this->once())
            ->method('decide')
            ->with($review, [$revision1], [$revision2])
            ->willReturn(CodeReviewType::COMMITS);
        $this->settingsProvider->expects($this->exactly(2))->method('getComparisonPolicy')->willReturn(DiffComparePolicy::IGNORE);
        $this->settingsProvider->expects($this->exactly(2))->method('getVisibleLines')->willReturn(6);
        $this->settingsProvider->expects($this->once())->method('getReviewDiffMode')->willReturn(ReviewDiffModeEnum::INLINE);
        $this->fileService->expects($this->once())
            ->method('getFiles')
            ->with(
                $review,
                [$revision2],
                'filepath',
                new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, DiffComparePolicy::IGNORE, CodeReviewType::COMMITS, 6)
            )
            ->willReturn([$fileTree, $selectedFile]);

        $expected = $this->createExpectation($review, $similarReviews, [$revision1], [$revision2], $fileTree, $selectedFile);
        $actual   = $this->provider->provide($review, $request);
        static::assertEquals($expected, $actual);
    }

    private function createRequest(): ReviewRequest&Stub
    {
        $request = static::createStub(ReviewRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getTab')->willReturn('tab');
        $request->method('getAction')->willReturn(null);

        return $request;
    }

    /**
     * @param DirectoryTreeNode<DiffFile> $fileTree
     * @param CodeReview[]                $similarReviews
     * @param Revision[]                  $revisions
     * @param Revision[]                  $visibleRevisions
     */
    private function createExpectation(
        CodeReview $review,
        array $similarReviews,
        array $revisions,
        array $visibleRevisions,
        DirectoryTreeNode $fileTree,
        DiffFile $selectedFile
    ): CodeReviewDto {
        return new CodeReviewDto(
            $review,
            $similarReviews,
            $revisions,
            $visibleRevisions,
            $fileTree,
            $selectedFile,
            'filepath',
            'tab',
            DiffComparePolicy::IGNORE,
            ReviewDiffModeEnum::INLINE,
            null,
            6
        );
    }
}
