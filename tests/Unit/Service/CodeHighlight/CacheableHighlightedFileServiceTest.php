<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeHighlight;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
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
     * @covers ::fromDiffFile
     * @throws InvalidArgumentException
     */
    public function testGetHighlightedFile(): void
    {
        $diffFile                = new DiffFile();
        $diffFile->filePathAfter = 'filePath';
        $diffFile->hashStart     = 'start';
        $diffFile->hashEnd       = 'end';
        $repository              = new Repository();
        $repository->setId(123);
        $hash = hash('sha256', 'highlight:fromDiffFile:123-filePath-startend');
        $file = new HighlightedFile($diffFile->filePathAfter, []);

        $this->cache->expects(self::once())->method('get')->with($hash)->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->fileService->expects(self::once())->method('fromDiffFile')->with($diffFile)->willReturn($file);

        static::assertSame($file, $this->service->fromDiffFile($repository, $diffFile));
    }
}
