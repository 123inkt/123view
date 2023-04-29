<?php
declare(strict_types=1);

namespace DR\Review\Service\Xml;

use DOMDocument;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;

class DOMDocumentFactory
{
    /**
     * @throws XMLException|ParseException
     */
    public function createFromString(string $data): DOMDocument
    {
        $document = new DOMDocument();
        if ($document->loadXML($data) === false) {
            $error = libxml_get_last_error();
            $error === false ? throw new ParseException('Unable to read xml. Invalid format') : throw new XMLException($error);
        }

        return $document;
    }
}
