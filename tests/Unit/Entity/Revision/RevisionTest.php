<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Revision;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Revision\Revision
 */
class RevisionTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new Revision())->getId());
        static::assertAccessorPairs(Revision::class);
    }
}
