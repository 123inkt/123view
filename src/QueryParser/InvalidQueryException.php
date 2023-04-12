<?php
declare(strict_types=1);

namespace DR\Review\QueryParser;

use DR\Review\QueryParser\Term\TermInterface;
use Exception;
use Parsica\Parsica\Internal\Fail;
use Parsica\Parsica\ParserHasFailed;

/**
 * @codeCoverageIgnore
 */
class InvalidQueryException extends Exception
{
    public function __construct(private readonly ParserHasFailed $exception)
    {
        parent::__construct($exception->getMessage(), $exception->code, $exception);
    }

    /**
     * @return Fail<TermInterface>
     */
    public function parseResult(): Fail
    {
        /** @var Fail<TermInterface> $result */
        $result = $this->exception->parseResult();

        return $result;
    }
}
