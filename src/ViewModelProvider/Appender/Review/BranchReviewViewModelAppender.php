<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Form\Review\ChangeBranchReviewBranchFormType;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\ViewModel\App\Review\BranchReviewViewModel;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

readonly class BranchReviewViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }

    /**
     * @inheritDoc
     */
    public function accepts(CodeReviewDto $dto, ReviewViewModel $viewModel): bool
    {
        return $dto->review->getType() === CodeReviewType::BRANCH;
    }

    /**
     * @throws Throwable
     */
    public function append(CodeReviewDto $dto, ReviewViewModel $viewModel): void
    {
        $form = $this->formFactory->create(
            ChangeBranchReviewBranchFormType::class,
            $dto->review,
            ['review' => $dto->review]
        );
        $viewModel->setBranchReviewViewModel(new BranchReviewViewModel($form->createView()));
    }
}
