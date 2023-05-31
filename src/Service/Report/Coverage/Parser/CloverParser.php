<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\Coverage\Parser;

use DOMElement;
use DOMXPath;
use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Entity\Report\LineCoverage;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Review\Utility\Assert;

class CloverParser implements CodeCoverageParserInterface
{
    public const FORMAT = 'clover';

    public function __construct(private readonly DOMDocumentFactory $documentFactory, private readonly FilePathNormalizer $pathNormalizer)
    {
    }

    /**
     * @return CodeCoverageFile[]
     * @throws XMLException|ParseException
     */
    public function parse(string $basePath, string $data): array
    {
        $result = [];

        // create document and search for file nodes
        $document     = $this->documentFactory->createFromString($data);
        $fileElements = Assert::notFalse((new DOMXpath($document))->query("/coverage/project/file"));

        /** @var DOMElement $fileElement */
        foreach ($fileElements as $fileElement) {
            $filePath   = $this->pathNormalizer->normalize($basePath, $fileElement->getAttribute('name'));
            $coverage   = new LineCoverage();
            $percentage = null;

            for ($node = $fileElement->firstElementChild; $node !== null; $node = $node->nextElementSibling) {
                if (strtolower($node->tagName) === 'line') {
                    // line node
                    $lineNumber  = (int)$node->getAttribute('num');
                    $coversCount = (int)$node->getAttribute('count');
                    $coverage->setCoverage($lineNumber, $coversCount);
                } elseif (strtolower($node->tagName) === 'metrics') {
                    // metrics node - calculate coverage percentage
                    $statements        = (int)$node->getAttribute('statements');
                    $coveredStatements = (int)$node->getAttribute('coveredstatements');
                    $percentage        = $statements === 0 ? 100 : round($coveredStatements / $statements * 100, 2);
                }
            }

            $result[] = (new CodeCoverageFile())
                ->setCoverage($coverage)
                ->setPercentage($percentage)
                ->setFile($filePath);
        }

        return $result;
    }
}
