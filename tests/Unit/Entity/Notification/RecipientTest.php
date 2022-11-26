<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Notification;

use DR\GitCommitNotification\Entity\Notification\Recipient;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Notification\Recipient
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
