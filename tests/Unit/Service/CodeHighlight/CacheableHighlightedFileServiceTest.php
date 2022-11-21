<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeHighlight;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Model\Review\Highlight\HighlightedFile;
use DR\GitCommitNotification\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\GitCommitNotification\Service\CodeHighlight\HighlightedFileService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeHighlight\CacheableHighlightedFileService
 * @covers ::__construct
 */
class CacheableHighlightedFileServiceTest extends AbstractTestCase
{
    private CacheInterface&MockObject         $cache;
    private HighlightedFileService&MockObject $fileService;
    private CacheableHighlightedFileService   $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache       = $this->createMock(CacheInterface::class);
        $this->fileService = $this->createMock(HighlightedFileService::class);
        $this->service     = new CacheableHighlightedFileService($this->cache, $this->fileService);
    }

    /**
     * @covers ::fromRevision
     * @throws InvalidArgumentException
     */
    public function testGetHighlightedFile(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setCommitHash('hash');
        $filePath = 'filePath';
        $hash     = hash('sha256', 'highlight:123-hash-filePath');
        $file     = new HighlightedFile($filePath, []);

        $this->cache->expects(self::once())->method('get')->with($hash)->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->fileService->expects(self::once())->method('fromRevision')->with($revision, $filePath)->willReturn($file);

        static::assertSame($file, $this->service->fromRevision($revision, $filePath));
    }
}
