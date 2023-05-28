<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\Coverage;

use DR\Review\Service\Report\Coverage\Parser\CodeCoverageParserInterface;
use InvalidArgumentException;
use Traversable;

class CodeCoverageParserProvider
{
    /** @var array<string, CodeCoverageParserInterface> */
    private array $parsers;

    /**
     * @param Traversable<string, CodeCoverageParserInterface> $parsers
     */
    public function __construct(Traversable $parsers)
    {
        $this->parsers = iterator_to_array($parsers);
    }

    public function getParser(string $format): CodeCoverageParserInterface
    {
        if (isset($this->parsers[$format]) === false) {
            throw new InvalidArgumentException('Unknown coverage format: ' . $format);
        }

        return $this->parsers[$format];
    }
}
