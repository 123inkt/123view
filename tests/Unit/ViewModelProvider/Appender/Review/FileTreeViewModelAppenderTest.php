<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider\Appender\Review;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\FileTreeViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\Appender\Review\FileTreeViewModelAppender;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

#[CoversClass(FileTreeViewModelAppender::class)]
class FileTreeViewModelAppenderTest extends AbstractTestCase
{
    private FormFactoryInterface&MockObject      $formFactory;
    private FileTreeViewModelProvider&MockObject $fileTreeModelProvider;
    private FileTreeViewModelAppender            $appender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formFactory           = $this->createMock(FormFactoryInterface::class);
        $this->fileTreeModelProvider = $this->createMock(FileTreeViewModelProvider::class);
        $this->appender              = new FileTreeViewModelAppender($this->formFactory, $this->fileTreeModelProvider);
    }

    public function testAccepts(): void
    {
        $dto       = $this->createDto();
        $viewModel = $this->createMock(ReviewViewModel::class);
        $viewModel->expects($this->once())->method('getSidebarTabMode')->willReturn(ReviewViewModel::SIDEBAR_TAB_OVERVIEW);
        $this->formFactory->expects($this->never())->method('create');
        $this->fileTreeModelProvider->expects($this->never())->method('getFileTreeViewModel');

        static::assertTrue($this->appender->accepts($dto, $viewModel));
    }

    public function testAppend(): void
    {
        $dto               = $this->createDto();
        $viewModel         = $this->createMock(ReviewViewModel::class);
        $form              = $this->createMock(FormInterface::class);
        $formView          = static::createStub(FormView::class);
        $fileTreeViewModel = static::createStub(FileTreeViewModel::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(AddReviewerFormType::class, null, ['review' => $dto->review])
            ->willReturn($form);
        $form->expects($this->once())->method('createView')->willReturn($formView);

        $this->fileTreeModelProvider->expects($this->once())
            ->method('getFileTreeViewModel')
            ->with($dto->review, $dto->fileTree, $dto->selectedFile)
            ->willReturn($fileTreeViewModel);

        $viewModel->expects($this->once())->method('setAddReviewerForm')->with($formView);
        $viewModel->expects($this->once())->method('setFileTreeModel')->with($fileTreeViewModel);

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
