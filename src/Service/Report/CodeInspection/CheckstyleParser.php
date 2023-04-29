<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection;

use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\Xml\DOMDocumentFactory;

class CheckstyleParser implements CodeInspectionParserInterface
{
    public function __construct(private readonly DOMDocumentFactory $documentFactory)
    {
    }

    /**
     * @inheritDoc
     * @throws XMLException|ParseException
     */
    public function parse(string $data): array
    {
        $document = $this->documentFactory->createFromString($data);
    }
}
