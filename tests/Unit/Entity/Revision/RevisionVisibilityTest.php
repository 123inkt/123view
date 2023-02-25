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
     * @covers ::getRevision
     * @covers ::setRevision
     * @covers ::getReview
     * @covers ::setReview
     * @covers ::getUser
     * @covers ::setUser
     * @covers ::isVisible
     * @covers ::setVisible
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RevisionVisibility::class);
    }
}
