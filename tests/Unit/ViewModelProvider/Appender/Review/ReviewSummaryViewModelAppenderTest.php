<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider\Appender\Review;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewSummaryViewModel;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\Appender\Review\ReviewSummaryViewModelAppender;
use DR\Review\ViewModelProvider\ReviewSummaryViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ReviewSummaryViewModelAppender::class)]
class ReviewSummaryViewModelAppenderTest extends AbstractTestCase
{
    private ReviewSummaryViewModelProvider&MockObject $summaryViewModelProvider;
    private ReviewSummaryViewModelAppender            $appender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->summaryViewModelProvider = $this->createMock(ReviewSummaryViewModelProvider::class);
        $this->appender                 = new ReviewSummaryViewModelAppender($this->summaryViewModelProvider);
    }

    public function testAccepts(): void
    {
        $this->summaryViewModelProvider->expects($this->never())->method('getSummaryViewModel');
        $dto       = $this->createDto();
        $viewModel = static::createStub(ReviewViewModel::class);

        static::assertTrue($this->appender->accepts($dto, $viewModel));
    }

    public function testAppend(): void
    {
        $dto              = $this->createDto();
        $viewModel        = $this->createMock(ReviewViewModel::class);
        $summaryViewModel = static::createStub(ReviewSummaryViewModel::class);

        $this->summaryViewModelProvider->expects($this->once())
            ->method('getSummaryViewModel')
            ->with($dto->review, $dto->revisions, $dto->fileTree)
            ->willReturn($summaryViewModel);

        $viewModel->expects($this->once())->method('setReviewSummaryViewModel')->with($summaryViewModel);
        $viewModel->expects($this->once())->method('setDescriptionVisible')->with(true);
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
            null,
            'string',
            'tab',
            DiffComparePolicy::ALL,
            ReviewDiffModeEnum::INLINE,
            null,
            6
        );
    }
}
