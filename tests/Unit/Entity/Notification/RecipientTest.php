<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\Recipient;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Notification\Recipient
 */
class RecipientTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new Recipient())->getId());
        static::assertAccessorPairs(Recipient::class);
    }
}
