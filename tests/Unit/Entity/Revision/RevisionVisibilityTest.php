<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Revision;

use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RevisionVisibility::class)]
class RevisionVisibilityTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RevisionVisibility::class);
    }
}
