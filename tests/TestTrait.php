<?php
declare(strict_types=1);

namespace DR\Review\Tests;

use Carbon\Carbon;
use DR\Review\Entity\Git\Author;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
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
            Carbon::now(),
            'subject',
            'refs',
            $files
        );
    }

    protected function createRepository(string $name, string $url): Repository
    {
        $repository = new Repository();
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
            ['DR\\Review\\Tests\\', 'DR\\Review\\Tests\\', 'DR\\Review\\Tests\\', '\\'],
            '/',
            implode('\\', explode('\\', get_class($this)))
        );

        return __DIR__ . '/Data' . $namespace . '/';
    }
}
