<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Tests\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(GitRepositoryLockManager::class)]
class GitRepositoryLockManagerTest extends AbstractTestCase
{
    private string                   $cacheDirectory;
    private Filesystem&MockObject    $filesystem;
    private GitRepositoryLockManager $lockManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheDirectory = vfsStream::setup('cache')->url() . '/git/';
        $this->filesystem     = $this->createMock(Filesystem::class);
        $this->lockManager    = new GitRepositoryLockManager($this->cacheDirectory, $this->filesystem);
    }

    public function testStartCacheDirectoryDoesNotExist(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('foobar');

        $this->filesystem->expects($this->once())
            ->method('mkdir')
            ->willReturnCallback(static fn($dir) => mkdir($dir, 0777, true));

        static::assertSame('result', $this->lockManager->start($repository, static fn() => 'result'));
    }

    public function testStartCacheDirectoryDoesExist(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('foobar');

        mkdir($this->cacheDirectory . '/git/', 0777, true);

        $this->filesystem->expects($this->never())->method('mkdir');

        static::assertSame('result', $this->lockManager->start($repository, static fn() => 'result'));
    }

    public function testStartShouldBubbleExceptions(): void
    {
        $this->filesystem->expects($this->never())->method('mkdir');
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('foobar');

        mkdir($this->cacheDirectory . '/git/', 0777, true);

        $this->expectException(RuntimeException::class);
        $this->lockManager->start($repository, static fn() => throw new RuntimeException());
    }

    public function testLockAcquiredReturnsTrueWhileInsideStart(): void
    {
        $repository = new Repository();
        $repository->setId(456);
        $repository->setName('test-repo');

        mkdir($this->cacheDirectory . '/git/', 0777, true);

        // filesystem->mkdir should not be called since the directory already exists
        $this->filesystem->expects($this->never())->method('mkdir');

        $acquiredInsideLock = false;

        $this->lockManager->start($repository, function () use ($repository, &$acquiredInsideLock): void {
            $acquiredInsideLock = $this->lockManager->lockAcquired($repository);
        });

        static::assertTrue($acquiredInsideLock);
        // After start() returns, lock should no longer be active
        static::assertFalse($this->lockManager->lockAcquired($repository));
    }

    public function testLockAcquiredReturnsFalseBeforeStart(): void
    {
        $this->filesystem->expects($this->never())->method('mkdir');

        $repository = new Repository();
        $repository->setId(789);
        $repository->setName('other-repo');

        static::assertFalse($this->lockManager->lockAcquired($repository));
    }
}
