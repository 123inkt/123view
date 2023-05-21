<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\Coverage;

use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Report\Coverage\Parser\CoberturaParser;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CoberturaParser::class)]
class CoberturaParserTest extends AbstractTestCase
{
    public function testParse(): void
    {
        $data = file_get_contents(__DIR__ . '/../../../../../coverage-coburtara.xml');

        $coberturaParser = new CoberturaParser(new DOMDocumentFactory(), new FilePathNormalizer());
        $coverage        = $coberturaParser->parse('C:\\Projects\\123view\\', $data);
    }
}
