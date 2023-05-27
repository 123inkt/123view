<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\Coverage\Parser;

use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Report\Coverage\Parser\CoberturaParser;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CoberturaParser::class)]
class CoberturaParserTest extends AbstractTestCase
{
    /**
     * @throws XMLException|ParseException
     */
    public function testParse(): void
    {
        $data = file_get_contents(__DIR__ . '/coverage-cobertura.xml');

        $coberturaParser = new CoberturaParser(new DOMDocumentFactory(), new FilePathNormalizer());
        $results         = $coberturaParser->parse('\\mnt\\123view\\', $data);
        static::assertCount(2, $results);

        $fileCoverage = $results[0];
        static::assertSame('src/ApiPlatform/Factory/CodeReviewActivityOutputFactory.php', $fileCoverage->getFile());

        $coverage = $fileCoverage->getCoverage();
        static::assertNotNull($coverage);
        static::assertSame(1, $coverage->getCoverage(12));
        static::assertNull($coverage->getCoverage(13));
        static::assertSame(0, $coverage->getCoverage(14));
    }
}
