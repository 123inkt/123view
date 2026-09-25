<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep\Iterator;

use DR\Review\Service\Search\RipGrep\Iterator\ProcessOutputIterator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProcessOutputIterator::class)]
class ProcessOutputIteratorTest extends AbstractTestCase
{
    public function testGetIterator(): void
    {
        $handle = popen(PHP_BINARY . ' -v', 'r');
        static::assertIsResource($handle);

        $iterator = new ProcessOutputIterator($handle);
        $output   = iterator_to_array($iterator);
        unset($iterator);

        static::assertCount(4, $output);

        // test if resource is closed
        static::assertFalse(is_resource($handle));
    }

    public function testGetIteratorWithEarlyTerminations(): void
    {
        $handle = popen(PHP_BINARY . ' -v', 'r');
        static::assertIsResource($handle);

        $iterator = new ProcessOutputIterator($handle);
        $firstLine = null;
        foreach ($iterator as $line) {
            $firstLine = $line;
            break;
        }
        unset($iterator);

        static::assertIsString($firstLine);

        // test if resource is closed
        static::assertFalse(is_resource($handle));
    }
}
