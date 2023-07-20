<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\Coverage\Parser;

use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Report\Coverage\Parser\CloverParser;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Review\Tests\AbstractTestCase;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CloverParser::class)]
class CloverParserTest extends AbstractTestCase
{
    /**
     * @throws XMLException|ParseException
     */
    public function testParse(): void
    {
        $data = Assert::notFalse(file_get_contents(__DIR__ . '/coverage-clover.xml'));

        $parser  = new CloverParser(new DOMDocumentFactory(), new FilePathNormalizer());
        $results = $parser->parse('\\mnt\\123view\\', $data);
        static::assertCount(2, $results);

        $fileCoverage = $results[0];
        static::assertEqualsWithDelta(73.33, $fileCoverage->getPercentage(), .01);
        static::assertSame('src/ApiPlatform/Factory/CodeReviewActivityOutputFactory.php', $fileCoverage->getFile());

        $coverage = $fileCoverage->getCoverage();
        static::assertNotNull($coverage);
        static::assertSame(1, $coverage->getCoverage(12));
        static::assertNull($coverage->getCoverage(13));
        static::assertSame(0, $coverage->getCoverage(14));
    }
}
