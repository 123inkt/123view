<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\CodeInspection;

use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Report\CodeInspection\Parser\CheckStyleIssueParser;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CheckStyleIssueParser::class)]
class CheckStyleIssueIssueParserTest extends AbstractTestCase
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
        $data = file_get_contents(__DIR__ . '/../../../../../checkstyle.xml');

        $issues = $this->parser->parse('C:\Projects\123view', $data);
    }
}
