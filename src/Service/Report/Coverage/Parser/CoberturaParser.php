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

class CoberturaParser implements CodeCoverageParserInterface
{
    public const FORMAT = 'cobertura';

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
            $filePath = $this->pathNormalizer->normalize($basePath, $fileElement->getAttribute('name'));
            $coverage = new LineCoverage();

            /** @var DOMElement $node */
            foreach ($fileElement->getElementsByTagName('line') as $node) {
                $lineNumber  = (int)$node->getAttribute('num');
                $coversCount = (int)$node->getAttribute('count');
                $coverage->setCoverage($lineNumber, $coversCount);
            }

            $result[] = (new CodeCoverageFile())
                ->setCoverage($coverage)
                ->setFile($filePath);
        }

        return $result;
    }
}
