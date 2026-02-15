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
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\Appender\Review\FileDiffViewModelAppender;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(FileDiffViewModelAppender::class)]
class FileDiffViewModelAppenderTest extends AbstractTestCase
{
    private FileDiffViewModelProvider&MockObject $fileDiffViewModelProvider;
    private FileDiffViewModelAppender            $appender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileDiffViewModelProvider = $this->createMock(FileDiffViewModelProvider::class);
        $this->appender                  = new FileDiffViewModelAppender($this->fileDiffViewModelProvider);
    }

    public function testAccepts(): void
    {
        $this->fileDiffViewModelProvider->expects($this->never())->method('getFileDiffViewModel');
        $dto       = $this->createDto();
        $viewModel = static::createStub(ReviewViewModel::class);

        static::assertTrue($this->appender->accepts($dto, $viewModel));
    }

    public function testAppend(): void
    {
        $dto               = $this->createDto();
        $viewModel         = $this->createMock(ReviewViewModel::class);
        $fileDiffViewModel = $this->createMock(FileDiffViewModel::class);

        $this->fileDiffViewModelProvider->expects($this->once())
            ->method('getFileDiffViewModel')
            ->with($dto->review, $dto->selectedFile, $dto->action, $dto->comparePolicy, ReviewDiffModeEnum::INLINE)
            ->willReturn($fileDiffViewModel);

        $fileDiffViewModel->expects($this->once())->method('setRevisions')->with($dto->visibleRevisions)->willReturnSelf();
        $viewModel->expects($this->once())->method('setFileDiffViewModel')->with($fileDiffViewModel);
        $viewModel->expects($this->once())->method('setDescriptionVisible')->with(false);
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
