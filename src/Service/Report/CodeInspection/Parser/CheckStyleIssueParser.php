<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection\Parser;

use DOMElement;
use DOMXPath;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Utils\Assert;

class CheckStyleIssueParser implements CodeInspectionIssueParserInterface
{
    public const FORMAT = 'checkstyle';

    public function __construct(private readonly DOMDocumentFactory $documentFactory, private readonly FilePathNormalizer $pathNormalizer)
    {
    }

    /**
     * @inheritDoc
     * @throws XMLException|ParseException
     */
    public function parse(string $basePath, string $subDirectory, string $data): array
    {
        $issues = [];

        // create document and search for file nodes
        $document     = $this->documentFactory->createFromString($data);
        $fileElements = Assert::notFalse((new DOMXpath($document))->query("/checkstyle/file"));

        /** @var DOMElement $fileElement */
        foreach ($fileElements as $fileElement) {
            $filePath = $this->pathNormalizer->normalize($basePath, $subDirectory, $fileElement->getAttribute('name'));

            /** @var DOMElement $errorElement */
            foreach ($fileElement->getElementsByTagName('error') as $errorElement) {
                $issues[] = $issue = new CodeInspectionIssue();
                $issue->setFile($filePath);
                $issue->setLineNumber((int)$errorElement->getAttribute('line'));
                $issue->setMessage($errorElement->getAttribute('message'));
                $issue->setSeverity($errorElement->getAttribute('severity'));
                $issue->setRule($errorElement->getAttribute('source'));
            }
        }

        return $issues;
    }
}
