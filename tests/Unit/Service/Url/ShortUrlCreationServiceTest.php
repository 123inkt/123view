<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Url;

use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\Entity\Url\ShortUrl;
use DR\Review\Repository\Url\ShortUrlRepository;
use DR\Review\Service\Url\ShortKeyGeneratorService;
use DR\Review\Service\Url\ShortUrlCreationService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ShortUrlCreationService::class)]
class ShortUrlCreationServiceTest extends AbstractTestCase
{
    use ClockTestTrait;

    private ShortUrlRepository&MockObject       $repository;
    private ShortKeyGeneratorService&MockObject $keyGenerator;
    private ShortUrlCreationService             $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository   = $this->createMock(ShortUrlRepository::class);
        $this->keyGenerator = $this->createMock(ShortKeyGeneratorService::class);
        $this->service      = new ShortUrlCreationService($this->repository, $this->keyGenerator);
    }

    /**
     * @throws Throwable
     */
    public function testCreateShortUrl(): void
    {
        $uri      = Http::new('/app/review/123/file/src/test.php');
        $shortKey = 'abc123';
        $expected = (new ShortUrl())->setShortKey($shortKey)->setOriginalUrl($uri)->setCreateTimestamp(self::now()->getTimestamp());

        $this->keyGenerator->expects($this->once())->method('generateUniqueShortKey')->willReturn($shortKey);
        $this->repository->expects($this->once())->method('save')->with($expected, true);

        $result = $this->service->createShortUrl($uri);
        static::assertEquals($expected, $result);
    }
}
