<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\Service\Git\Branch\CacheableGitBranchService;
use DR\Review\ViewModel\App\Review\BranchReviewViewModel;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use Throwable;

readonly class BranchReviewViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(private CacheableGitBranchService $branchService)
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
        $branches = $this->branchService->getRemoteBranches($dto->review->getRepository());

        // remove origin/ prefix
        $branches = array_map(static fn(string $branch): string => str_replace('origin/', '', $branch), $branches);

        // filter out HEAD
        $branches = array_filter($branches, static fn(string $branch): bool => $branch !== 'HEAD');

        $viewModel->setBranchReviewViewModel(new BranchReviewViewModel($branches));
    }
}
