<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\CodeInspection\Parser;

use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Report\CodeInspection\Parser\JunitIssueParser;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JunitIssueParser::class)]
class JunitIssueParserTest extends AbstractTestCase
{
    private JunitIssueParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new JunitIssueParser(new DOMDocumentFactory(), new FilePathNormalizer());
    }

    /**
     * @throws XMLException|ParseException
     */
    public function testParse(): void
    {
        $data = (string)file_get_contents(__DIR__ . '/JunitIssueParserTest.xml');

        $issues = $this->parser->parse('/builds/123/production/drs/drs/', 'subDir', $data);

        static::assertCount(3, $issues);

        static::assertSame('subDir/file/to/test-with-warning.php', $issues[0]->getFile());
        static::assertSame('subDir/file/to/test-with-failure.php', $issues[1]->getFile());
        static::assertSame('subDir/file/to/test-with-error.php', $issues[2]->getFile());

        static::assertSame(20, $issues[0]->getLineNumber());
        static::assertSame('warning', $issues[0]->getSeverity());
        static::assertSame('warning', $issues[0]->getRule());
        static::assertSame('test warning', $issues[0]->getMessage());
    }
}
