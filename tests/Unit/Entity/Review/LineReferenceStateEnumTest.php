<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LineReferenceStateEnum::class)]
class LineReferenceStateEnumTest extends AbstractTestCase
{
    public function testValues(): void
    {
        static::assertSame(['M', 'U', 'A', 'D', '?'], LineReferenceStateEnum::values());
    }
}
