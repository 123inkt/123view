<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests;

use DateTime;
use DR\GitCommitNotification\Entity\Config\RepositoryReference;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Repository;
use SplFileInfo;

trait TestTrait
{
    protected function getFilePath(string $relativeFilePath): SplFileInfo
    {
        return new SplFileInfo($this->getDataDir() . $relativeFilePath);
    }

    /**
     * @param DiffFile[] $files
     */
    protected function createCommit(?Author $author = null, array $files = []): Commit
    {
        return new Commit(
            new Repository(),
            'parent-hash',
            'commit-hash',
            $author ?? new Author('name', 'email'),
            new DateTime(),
            'subject',
            'refs',
            $files
        );
    }

    protected function createRepository(string $name, string $url): Repository
    {
        $repository       = new Repository();
        $repository->setName($name);
        $repository->setUrl($url);

        return $repository;
    }

    protected function createRepositoryReference(string $name): RepositoryReference
    {
        $reference       = new RepositoryReference();
        $reference->name = $name;

        return $reference;
    }

    protected function getFileContents(string $relativeFilePath): string
    {
        $path = $this->getDataDir() . $relativeFilePath;

        return (string)file_get_contents($path);
    }

    private function getDataDir(): string
    {
        $namespace = str_replace(
            ['DR\\GitCommitNotification\\Tests\\', 'DR\\GitCommitNotification\\Tests\\', 'DR\\GitCommitNotification\\Tests\\', '\\'],
            '/',
            implode('\\', explode('\\', get_class($this)))
        );

        return __DIR__ . '/Data' . $namespace . '/';
    }
}
