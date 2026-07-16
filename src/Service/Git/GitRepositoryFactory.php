<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Git\GitRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

readonly class GitRepositoryFactory
{
    public function __construct(private LoggerInterface $gitLogger, private ?Stopwatch $stopwatch)
    {
    }

    public function create(Repository $repository, string $repositoryPath): GitRepository
    {
        return new GitRepository($this->gitLogger, $repository, $this->stopwatch, $repositoryPath);
    }
}
