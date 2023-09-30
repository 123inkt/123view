<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\CodeInspection\Parser;

use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Report\CodeInspection\Parser\CheckStyleIssueParser;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CheckStyleIssueParser::class)]
class CheckStyleIssueParserTest extends AbstractTestCase
{
    private CheckStyleIssueParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CheckStyleIssueParser(new DOMDocumentFactory(), new FilePathNormalizer());
    }

    /**
     * @throws XMLException|ParseException
     */
    public function testParse(): void
    {
        $data = (string)file_get_contents(__DIR__ . '/CheckStyleIssueParserTest.xml');

        $issues = $this->parser->parse('/build/5', 'subDir', $data);

        static::assertCount(1, $issues);
        static::assertSame('subDir/src/file/path.php', $issues[0]->getFile());
        static::assertSame(1, $issues[0]->getLineNumber());
        static::assertSame('message', $issues[0]->getMessage());
        static::assertSame('error', $issues[0]->getSeverity());
        static::assertSame('source', $issues[0]->getRule());
    }
}
