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

class JunitIssueParser implements CodeInspectionIssueParserInterface
{
    public const FORMAT = 'junit';

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

        // create document and search for error, failure and warning nodes
        $document      = $this->documentFactory->createFromString($data);
        $issueElements = Assert::notFalse((new DOMXpath($document))->query("//error | //failure | //warning"));

        /** @var DOMElement $issueElement */
        foreach ($issueElements as $issueElement) {
            // expecting parentElement to be testcase
            $testCaseElement = $issueElement->parentNode;
            if ($testCaseElement === null || $testCaseElement instanceof DOMElement === false || $testCaseElement->nodeName !== 'testcase') {
                continue;
            }

            $filePath = $subDirectory . $this->pathNormalizer->normalize($basePath, $subDirectory, $testCaseElement->getAttribute('file'));

            $issues[] = $issue = new CodeInspectionIssue();
            $issue->setFile($filePath);
            $issue->setLineNumber((int)$testCaseElement->getAttribute('line'));
            $issue->setMessage(Assert::string(preg_replace('/[ ]+/', ' ', str_replace($basePath, '', trim($issueElement->textContent)))));
            $issue->setSeverity(strtolower((string)$issueElement->nodeName));
            $issue->setRule((string)$issueElement->getAttribute('type'));
        }

        return $issues;
    }
}
