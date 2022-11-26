<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Utility\Assert;
use Symfony\Component\Filesystem\Filesystem;

class GitRepositoryLockManager
{
    private string $cacheDirectory;

    public function __construct(string $cacheDirectory, private readonly Filesystem $filesystem)
    {
        $this->cacheDirectory = $cacheDirectory . '/git/';
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function start(Repository $repository, callable $callback): mixed
    {
        $lockfile = sprintf('%s%s.%s.lock', $this->cacheDirectory, $repository->getId(), $repository->getName());
        if (is_dir(dirname($lockfile)) === false) {
            $this->filesystem->mkdir(dirname($lockfile));
        }

        $fileHandle = Assert::notFalse(fopen($lockfile, "wb+"));

        try {
            flock($fileHandle, LOCK_EX);

            return $callback();
        } finally {
            flock($fileHandle, LOCK_UN);
        }
    }
}
