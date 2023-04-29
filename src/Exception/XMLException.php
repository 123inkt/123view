<?php
declare(strict_types=1);

namespace DR\Review\Exception;

use Exception;
use LibXMLError;
use Throwable;

class XMLException extends Exception
{
    private string $xmlFile;
    private int    $xmlLine;

    public function __construct(LibXMLError $error, ?Throwable $previous = null)
    {
        parent::__construct($error->message, $error->code, $previous);
        $this->xmlFile = $error->file;
        $this->xmlLine = $error->line;
    }

    public function getXmlFile(): string
    {
        return $this->xmlFile;
    }

    public function getXmlLine(): int
    {
        return $this->xmlLine;
    }
}
