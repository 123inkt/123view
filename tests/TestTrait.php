<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests;

use DateTime;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
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

    protected function getFileContents(string $relativeFilePath): string
    {
        $path = $this->getDataDir() . $relativeFilePath;

        return (string)file_get_contents($path);
    }

    protected function loadFixture(string $fixture): mixed
    {
        return require $this->getDataDir() . '/Fixtures/' . $fixture;
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
