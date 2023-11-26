<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\Recipient;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Recipient::class)]
class RecipientTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertNull((new Recipient())->getId());
        static::assertAccessorPairs(Recipient::class);
    }
}
