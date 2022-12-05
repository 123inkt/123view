<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Git\Diff\DiffChange
 */
class DiffChangeTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $change = new DiffChange(DiffChange::REMOVED, 'foobar');
        static::assertSame(DiffChange::REMOVED, $change->type);
        static::assertSame('foobar', $change->code);
    }
}
