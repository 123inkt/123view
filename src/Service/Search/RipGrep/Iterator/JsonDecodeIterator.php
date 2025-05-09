<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep\Iterator;

use IteratorAggregate;
use Nette\Utils\Json;
use Traversable;

/**
 * @phpstan-type SearchResultEntry array{
 *     type: 'begin'|'context'|'match'|'end',
 *     data: array{
 *         path: array{text: string},
 *         lines: array{text: string},
 *         line_number: int
 *     }
 * }
 * @implements IteratorAggregate<int, SearchResultEntry>
 */
class JsonDecodeIterator implements IteratorAggregate
{
    /**
     * @param iterable<int, string> $iterator
     */
    public function __construct(private readonly iterable $iterator)
    {
    }

    public function getIterator(): Traversable
    {
        foreach ($this->iterator as $key => $value) {
            /** @var array{
             *     type: 'begin'|'context'|'end'|'match',
             *     data: array{path: array{text: string}, lines: array{text: string}, line_number: int}
             * } $json */
            $json = Json::decode($value, true);
            yield $key => $json;
        }
    }
}
