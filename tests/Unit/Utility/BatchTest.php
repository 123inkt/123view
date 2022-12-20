<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\Batch;
use stdClass;

/**
 * @coversDefaultClass \DR\Review\Utility\Batch
 * @covers ::__construct
 */
class BatchTest extends AbstractTestCase
{
    /**
     * @covers ::addAll
     * @covers ::add
     * @covers ::flush
     */
    public function testBatch(): void
    {
        $count = 0;
        $batch = new Batch(
            2,
            static function ($entities) use (&$count): void { // @codingStandardsIgnoreLine
                $count += count($entities);
            }
        );

        $batch->add(new stdClass());
        $batch->addAll([new stdClass(), new stdClass()]);
        static::assertSame(2, $count);

        $batch->flush();
        static::assertSame(3, $count);
    }
}
