<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Helper;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Service\Git\GitRepositoryLockManager;
use Symfony\Component\Filesystem\Filesystem;

class MockGitRepositoryLockManager extends GitRepositoryLockManager
{
    public function __construct()
    {
        parent::__construct('/tmp/', new Filesystem());
    }

    public function start(Repository $repository, callable $callback): mixed
    {
        return $callback();
    }
}
