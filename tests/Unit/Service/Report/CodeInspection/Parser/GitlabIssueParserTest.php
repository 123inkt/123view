<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\CodeInspection\Parser;

use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Service\Report\CodeInspection\Parser\GitlabIssueParser;
use DR\Review\Tests\AbstractTestCase;
use Nette\Utils\JsonException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitlabIssueParser::class)]
class GitlabIssueParserTest extends AbstractTestCase
{
    private GitlabIssueParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new GitlabIssueParser(new FilePathNormalizer());
    }

    /**
     * @throws JsonException
     */
    public function testParse(): void
    {
        $data = (string)file_get_contents(__DIR__ . '/GitlabIssueParserTest.json');

        $issues = $this->parser->parse('/build/5', 'subDir', $data);

        static::assertCount(1, $issues);
        static::assertSame('subDir/src/file/path.php', $issues[0]->getFile());
        static::assertSame(1, $issues[0]->getLineNumber());
        static::assertSame('message', $issues[0]->getMessage());
        static::assertSame('error', $issues[0]->getSeverity());
        static::assertSame('source', $issues[0]->getRule());
    }
}
