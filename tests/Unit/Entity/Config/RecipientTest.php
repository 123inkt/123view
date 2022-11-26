<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Recipient
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
