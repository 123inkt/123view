<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Revision;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Revision::class)]
class RevisionTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertNull((new Revision())->getId());
        static::assertAccessorPairs(Revision::class);
    }
}
