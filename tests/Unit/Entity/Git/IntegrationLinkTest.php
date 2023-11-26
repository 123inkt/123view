<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git;

use DR\Review\Entity\Git\IntegrationLink;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IntegrationLink::class)]
class IntegrationLinkTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $link = new IntegrationLink('url', 'image', 'text');
        static::assertSame('url', $link->url);
        static::assertSame('image', $link->image);
        static::assertSame('text', $link->text);
    }
}
