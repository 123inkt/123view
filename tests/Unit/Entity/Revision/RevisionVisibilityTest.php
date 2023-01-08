<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Revision;

use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Revision\RevisionVisibility
 */
class RevisionVisibilityTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RevisionVisibility::class);
    }
}
