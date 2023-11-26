<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RuleOptions::class)]
class RuleOptionsTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertNull((new RuleOptions())->getId());
        static::assertAccessorPairs(RuleOptions::class);
    }
}
