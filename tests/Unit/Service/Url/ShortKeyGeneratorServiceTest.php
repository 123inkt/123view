<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Url;

use DR\Review\Entity\Url\ShortUrl;
use DR\Review\Repository\Url\ShortUrlRepository;
use DR\Review\Service\Url\ShortKeyGeneratorService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Throwable;

#[CoversClass(ShortKeyGeneratorService::class)]
class ShortKeyGeneratorServiceTest extends AbstractTestCase
{
    private ShortUrlRepository&MockObject $repository;
    private ShortKeyGeneratorService      $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ShortUrlRepository::class);
        $this->service    = new ShortKeyGeneratorService($this->repository);
    }

    /**
     * @throws Throwable
     */
    public function testGenerateRandomKeyWithMinimumLength(): void
    {
        $key = $this->service->generateRandomKey(4);

        static::assertSame(4, strlen($key));
        static::assertMatchesRegularExpression('/^[A-Za-z0-9_.+\-]+$/', $key);
    }

    /**
     * @throws Throwable
     */
    public function testGenerateRandomKeyWithLongerLength(): void
    {
        $key = $this->service->generateRandomKey(10);

        static::assertSame(10, strlen($key));
        static::assertMatchesRegularExpression('/^[A-Za-z0-9_.+\-]+$/', $key);
    }

    /**
     * @throws Throwable
     */
    public function testGenerateUniqueShortKeyReturnsUniqueKey(): void
    {
        // Mock repository to return null (key doesn't exist)
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $shortKey = $this->service->generateUniqueShortKey();

        static::assertIsString($shortKey);
        static::assertGreaterThanOrEqual(4, strlen($shortKey));
        static::assertMatchesRegularExpression('/^[A-Za-z0-9_.+\-]+$/', $shortKey);
    }

    /**
     * @throws Throwable
     */
    public function testGenerateUniqueShortKeyRetriesWhenKeyExists(): void
    {
        // First call returns a ShortUrl (key exists), second call returns null (unique key found)
        $this->repository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls(
                $this->createMock(ShortUrl::class), // Key exists
                null // Key is unique
            );

        $shortKey = $this->service->generateUniqueShortKey();

        static::assertIsString($shortKey);
        static::assertGreaterThanOrEqual(4, strlen($shortKey));
    }

    /**
     * @throws Throwable
     */
    public function testConstructorWithCustomMaxAttempts(): void
    {
        $service = new ShortKeyGeneratorService($this->repository, 10);

        // Mock repository to always return existing keys
        $this->repository->expects($this->atLeast(1))
            ->method('findOneBy')
            ->willReturn($this->createMock(ShortUrl::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to generate unique short key after maximum attempts');

        $service->generateUniqueShortKey();
    }
}
