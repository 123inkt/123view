<?php
declare(strict_types=1);

namespace DR\Review\Git;

use DR\Review\Entity\Repository\Repository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class GitRepositoryFactory
{
    public function create(LoggerInterface $logger, Repository $repository, ?Stopwatch $stopwatch, string $repositoryPath): GitRepository
    {
        return new GitRepository($logger, $repository, $stopwatch, $repositoryPath);
    }
}
