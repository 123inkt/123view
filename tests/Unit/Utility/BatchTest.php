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
        $classA = new stdClass();
        $classB = new stdClass();
        $classC = new stdClass();

        $shouldBeCalled = $this->getMockBuilder(stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
        $shouldBeCalled->expects($this->exactly(2))
            ->method('__invoke')
            ->withConsecutive([[$classA, $classB]], [[$classC]]);

        $batch = new Batch(2, [$shouldBeCalled, '__invoke']);

        $batch->add($classA);
        $batch->addAll([$classB, $classC]);
        $batch->flush();
    }
}
