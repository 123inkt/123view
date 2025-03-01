<?php
declare(strict_types=1);

namespace DR\Review\Command\Review;

use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Branch\LockableGitBranchService;
use DR\Review\Service\Webhook\ReviewEventService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('review:close-branch-reviews', "Close all branch review for which the branch no longer exists")]
class BranchReviewCloseCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly LockableGitBranchService $branchService,
        private readonly ReviewEventService $reviewEventService,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @throws RepositoryException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repositories = $this->repository->findBy(['active' => true]);
        $this->logger?->info('Checking {count} repositories for abandoned branch reviews', ['count' => count($repositories)]);

        $closed = 0;
        foreach ($repositories as $repository) {
            $this->logger?->debug('Check {repository}', ['repository' => $repository->getName()]);
            $reviews = $this->reviewRepository->findBy(['repository' => $repository, 'type' => 'branch', 'state' => 'open']);

            if (count($reviews) === 0) {
                $this->logger?->info('No open branch reviews for repository {repository}', ['repository' => $repository->getName()]);
                continue;
            }

            // get all current branches. key by branch name
            $branches = array_flip($this->branchService->getRemoteBranches($repository));

            $this->logger?->debug('Found {count} open branch reviews', ['count' => count($reviews)]);
            foreach ($reviews as $review) {
                if (isset($branches[$review->getReferenceId()])) {
                    continue;
                }

                $this->logger?->info(
                    'Closing review: {repository} CR-{review}',
                    ['repository' => $repository->getName(), 'review' => $review->getProjectId()]
                );

                $reviewState = $review->getState();
                $this->reviewRepository->save($review->setState(CodeReviewStateType::CLOSED), true);
                $this->reviewEventService->reviewStateChanged($review, (string)$reviewState, null);
                ++$closed;
            }
        }

        $this->logger?->info('Closed {count} branch reviews', ['count' => $closed]);

        return self::SUCCESS;
    }
}
