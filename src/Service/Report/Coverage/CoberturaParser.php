<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\Coverage;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMXPath;
use DR\Review\Entity\Report\Coverage\FileCoverage;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Utility\Assert;
use LibXMLError;

class CoberturaParser
{
    /**
     * @return FileCoverage[]
     * @throws DOMException
     */
    public function parse(Repository $repository, string $test): array
    {
        $result = [];

        $document = new DOMDocument();
        if ($document->loadXML($test) === false) {
            $error = libxml_get_last_error();
            throw new DOMException(sprintf('Failed to parse xml: %s', $error instanceof LibXMLError ? $error->message : ''));
        }

        // search file elements
        $fileElements = Assert::notFalse((new DOMXpath($document))->query("/coverage/project/file"));

        /** @var DOMElement $fileElement */
        foreach ($fileElements as $fileElement) {
            $result[] = $fileCoverage = new FileCoverage();
            $fileCoverage->setFilePath($fileElement->getAttribute('name'));
            $fileCoverage->setRepository($repository);
            $fileCoverage->setCreateTimestamp(time());

            /** @var DOMElement $node */
            foreach ($fileElement->getElementsByTagName('line') as $node) {
                if ($node->getAttribute('type') === 'stmt' && $node->getAttribute('count') === '1') {
                    $fileCoverage->getCoverage()->set((int)$node->getAttribute('num'));
                }
            }
        }

        return $result;
    }
}
