<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeHighlight;

use Closure;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\Review\Service\CodeHighlight\HighlightedFileService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\CacheItem;

#[CoversClass(CacheableHighlightedFileService::class)]
class CacheableHighlightedFileServiceTest extends AbstractTestCase
{
    private AbstractAdapter&MockObject        $cache;
    private HighlightedFileService&MockObject $fileService;
    private CacheableHighlightedFileService   $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache       = $this->createMock(AbstractAdapter::class);
        $this->fileService = $this->createMock(HighlightedFileService::class);
        $this->service     = new CacheableHighlightedFileService($this->cache, $this->fileService);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetHighlightedFileCacheHit(): void
    {
        $diffFile                = new DiffFile();
        $diffFile->filePathAfter = 'filePath';
        $diffFile->hashStart     = 'start';
        $diffFile->hashEnd       = 'end';
        $repository              = new Repository();
        $repository->setId(123);
        $hash = hash('sha256', 'highlight:fromDiffFile:123-filePath-startend');

        $cacheItem = Closure::bind(
            static function () {
                $item        = new CacheItem();
                $item->value = [5 => 'foobar'];
                $item->isHit = true;

                return $item;
            },
            null,
            CacheItem::class
        )();

        $this->cache->expects($this->once())->method('getItem')->with($hash)->willReturn($cacheItem);
        $this->cache->expects($this->never())->method('get');
        $this->fileService->expects($this->never())->method('fromDiffFile');

        $actual = $this->service->fromDiffFile($repository, $diffFile);
        static::assertNotNull($actual);
        static::assertSame('filePath', $actual->filePath);
        static::assertSame([5 => 'foobar'], ($actual->closure)());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetHighlightedFileCacheMiss(): void
    {
        $diffFile                = new DiffFile();
        $diffFile->filePathAfter = 'filePath';
        $diffFile->hashStart     = 'start';
        $diffFile->hashEnd       = 'end';
        $repository              = new Repository();
        $repository->setId(123);
        $hash = hash('sha256', 'highlight:fromDiffFile:123-filePath-startend');
        $file = new HighlightedFile($diffFile->filePathAfter, static fn() => [5 => 'foobar']);

        $cacheItem = new CacheItem();
        $cacheItem->set(null);

        $this->cache->expects($this->once())->method('getItem')->with($hash)->willReturn($cacheItem);
        $this->fileService->expects($this->once())->method('fromDiffFile')->with($diffFile)->willReturn($file);
        $this->cache->expects($this->once())->method('get')->with($hash)->willReturnCallback(static fn($repository, $callback) => $callback());

        $actual = $this->service->fromDiffFile($repository, $diffFile);
        static::assertNotNull($actual);
        static::assertSame('filePath', $actual->filePath);
        static::assertSame([5 => 'foobar'], ($actual->closure)());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetHighlightedFileFailure(): void
    {
        $diffFile                = new DiffFile();
        $diffFile->filePathAfter = 'filePath';
        $diffFile->hashStart     = 'start';
        $diffFile->hashEnd       = 'end';
        $repository              = new Repository();
        $repository->setId(123);
        $hash = hash('sha256', 'highlight:fromDiffFile:123-filePath-startend');

        $cacheItem = new CacheItem();
        $cacheItem->set(null);

        $this->cache->expects($this->once())->method('getItem')->with($hash)->willReturn($cacheItem);
        $this->fileService->expects($this->once())->method('fromDiffFile')->with($diffFile)->willReturn(null);
        $this->cache->expects($this->never())->method('get')->with($hash);

        static::assertNull($this->service->fromDiffFile($repository, $diffFile));
    }
}
