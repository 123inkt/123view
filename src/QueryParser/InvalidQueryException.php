<?php
declare(strict_types=1);

namespace DR\Review\QueryParser;

use Exception;
use Parsica\Parsica\Internal\Fail;
use Parsica\Parsica\ParserHasFailed;

class InvalidQueryException extends Exception
{
    public function __construct(private readonly ParserHasFailed $exception)
    {
        parent::__construct($exception->getMessage(), $exception->code, $exception);
    }

    public function parseResult(): Fail
    {
        return $this->exception->parseResult();
    }
}
