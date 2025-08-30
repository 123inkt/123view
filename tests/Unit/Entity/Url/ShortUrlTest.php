<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Url;

use DR\Review\Entity\Url\ShortUrl;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ShortUrl::class)]
class ShortUrlTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(ShortUrl::class);
    }
}