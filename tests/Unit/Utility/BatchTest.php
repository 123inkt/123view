<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Tests\Helper\MockCallableClass;
use DR\Review\Utility\Batch;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(Batch::class)]
class BatchTest extends AbstractTestCase
{
    public function testBatch(): void
    {
        $classA = new stdClass();
        $classB = new stdClass();
        $classC = new stdClass();

        $shouldBeCalled = $this->createMock(MockCallableClass::class);
        $shouldBeCalled->expects($this->exactly(2))
            ->method('__invoke')
            ->with(...consecutive([[$classA, $classB]], [[$classC]]));

        $batch = new Batch(2, $shouldBeCalled);

        $batch->add($classA);
        $batch->addAll([$classB, $classC]);
        $batch->flush();
    }
}
