<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection;

use DR\Review\Service\Report\CodeInspection\Parser\CodeInspectionIssueParserInterface;
use InvalidArgumentException;
use Traversable;

class CodeInspectionIssueParserProvider
{
    /** @var array<string, CodeInspectionIssueParserInterface> */
    private array $parsers;

    /**
     * @param Traversable<string, CodeInspectionIssueParserInterface> $parsers
     */
    public function __construct(Traversable $parsers)
    {
        $this->parsers = iterator_to_array($parsers);
    }

    public function getParser(string $format): CodeInspectionIssueParserInterface
    {
        if (isset($this->parsers[$format]) === false) {
            throw new InvalidArgumentException('Unknown inspection: ' . $format);
        }

        return $this->parsers[$format];
    }
}
