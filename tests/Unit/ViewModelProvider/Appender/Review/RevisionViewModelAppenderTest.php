<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider\Appender\Review;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModel\App\Revision\ReviewRevisionViewModel;
use DR\Review\ViewModelProvider\Appender\Review\RevisionViewModelAppender;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RevisionViewModelAppender::class)]
class RevisionViewModelAppenderTest extends AbstractTestCase
{
    private RevisionViewModelProvider&MockObject $revisionModelProvider;
    private RevisionViewModelAppender            $appender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revisionModelProvider = $this->createMock(RevisionViewModelProvider::class);
        $this->appender              = new RevisionViewModelAppender($this->revisionModelProvider);
    }

    public function testAccepts(): void
    {
        $dto       = $this->createDto();
        $viewModel = $this->createMock(ReviewViewModel::class);
        $viewModel->expects($this->once())->method('getSidebarTabMode')->willReturn(ReviewViewModel::SIDEBAR_TAB_REVISIONS);

        static::assertTrue($this->appender->accepts($dto, $viewModel));
    }

    public function testAppend(): void
    {
        $dto               = $this->createDto();
        $viewModel         = $this->createMock(ReviewViewModel::class);
        $revisionViewModel = $this->createMock(ReviewRevisionViewModel::class);

        $this->revisionModelProvider->expects($this->once())
            ->method('getRevisionViewModel')
            ->with($dto->review, $dto->revisions)
            ->willReturn($revisionViewModel);

        $viewModel->expects($this->once())->method('setRevisionViewModel')->with($revisionViewModel);

        $this->appender->append($dto, $viewModel);
    }

    private function createDto(): CodeReviewDto
    {
        $revision = new Revision();

        return new CodeReviewDto(
            new CodeReview(),
            [],
            [$revision],
            [$revision],
            new DirectoryTreeNode('name'),
            new DiffFile(),
            'string',
            'tab',
            DiffComparePolicy::ALL,
            ReviewDiffModeEnum::INLINE,
            null,
            6
        );
    }
}
