<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Service\Git\GitRepositoryLockManager;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\GitRepositoryLockManager
 * @covers ::__construct
 */
class GitRepositoryLockManagerTest extends AbstractTestCase
{
    private string                   $cacheDirectory;
    private Filesystem&MockObject    $filesystem;
    private GitRepositoryLockManager $lockManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheDirectory = vfsStream::setup('cache')->url();
        $this->filesystem     = $this->getMockBuilder(Filesystem::class)->enableProxyingToOriginalMethods()->getMock();
        $this->lockManager    = new GitRepositoryLockManager($this->cacheDirectory, $this->filesystem);
    }

    /**
     * @covers ::start
     */
    public function testStartCacheDirectoryDoesNotExist(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('foobar');

        $this->filesystem->expects(self::once())->method('mkdir');

        static::assertSame('result', $this->lockManager->start($repository, static fn() => 'result'));
    }

    /**
     * @covers ::start
     */
    public function testStartCacheDirectoryDoesExist(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('foobar');

        mkdir($this->cacheDirectory . '/git/', 0777, true);

        $this->filesystem->expects(self::never())->method('mkdir');

        static::assertSame('result', $this->lockManager->start($repository, static fn() => 'result'));
    }

    /**
     * @covers ::start
     */
    public function testStartShouldBubbleExceptions(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setName('foobar');

        $this->expectException(RuntimeException::class);
        $this->lockManager->start($repository, static fn() => throw new RuntimeException());
    }
}
