<?php
declare(strict_types=1);

namespace DR\Review\Tests\Helper;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
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
