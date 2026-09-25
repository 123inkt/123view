<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep\Iterator;

use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;
use DR\Review\Tests\AbstractTestCase;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JsonDecodeIterator::class)]
class JsonDecodeIteratorTest extends AbstractTestCase
{
    public function testGetIterator(): void
    {
        $iterator = new JsonDecodeIterator($this->getIterator());
        $result   = iterator_to_array($iterator);
        $expected = [
            [
                'type' => 'begin',
                'data' => [
                    'path'        => ['text' => 'foo'],
                    'lines'       => ['text' => 'bar'],
                    'line_number' => 1,
                ],
            ],
        ];
        static::assertSame($expected, $result);
    }

    private function getIterator(): Generator
    {
        yield '{"type":"begin","data":{"path":{"text":"foo"},"lines":{"text":"bar"},"line_number":1}}';
    }
}
