<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider\Appender\Review;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Form\Review\ChangeBranchReviewBranchFormType;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\BranchReviewViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\Appender\Review\BranchReviewViewModelAppender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

#[CoversClass(BranchReviewViewModelAppender::class)]
class BranchReviewViewModelAppenderTest extends AbstractTestCase
{
    private FormFactoryInterface&MockObject $formFactory;
    private BranchReviewViewModelAppender   $appender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->appender    = new BranchReviewViewModelAppender($this->formFactory);
    }

    public function testAccepts(): void
    {
        $dto       = $this->createDto();
        $viewModel = $this->createMock(ReviewViewModel::class);

        static::assertTrue($this->appender->accepts($dto, $viewModel));
    }

    public function testAppend(): void
    {
        $dto       = $this->createDto();
        $viewModel = $this->createMock(ReviewViewModel::class);
        $form      = $this->createMock(FormInterface::class);
        $formView  = $this->createMock(FormView::class);

        $this->formFactory->expects($this->once())->method('create')
            ->with(ChangeBranchReviewBranchFormType::class, $dto->review, ['review' => $dto->review])
            ->willReturn($form);
        $form->expects($this->once())->method('createView')->willReturn($formView);
        $viewModel->expects($this->once())->method('setBranchReviewViewModel')->with(new BranchReviewViewModel($formView));

        $this->appender->append($dto, $viewModel);
    }

    private function createDto(): CodeReviewDto
    {
        $revision = new Revision();

        return new CodeReviewDto(
            (new CodeReview())->setType(CodeReviewType::BRANCH),
            [$revision],
            [$revision],
            new DirectoryTreeNode('name'),
            null,
            'string',
            'tab',
            DiffComparePolicy::ALL,
            ReviewDiffModeEnum::INLINE,
            null
        );
    }
}
