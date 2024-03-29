<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\CherryPick;

use DR\Review\Service\Git\CherryPick\GitCherryPickParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitCherryPickParser::class)]
class GitCherryPickParserTest extends AbstractTestCase
{
    private GitCherryPickParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new GitCherryPickParser();
    }

    public function testParseWithNoCherryPick(): void
    {
        $data = "no cherry-pick or revert in progress\n";

        $result = $this->parser->parse($data);
        static::assertTrue($result->completed);
        static::assertSame([], $result->conflicts);
    }

    public function testParseWithRename(): void
    {
        $data = "some random text\n";
        $data .= "  CONFLICT (rename/delete): src/Tests/Unit/AbstractTest.php renamed to src/Tests/Unit/AbstractTestCase.php in c61a64dc1de.";
        $data .= "some random text\n";

        $result = $this->parser->parse($data);
        static::assertFalse($result->completed);
        static::assertSame(['src/Tests/Unit/AbstractTestCase.php'], $result->conflicts);
    }

    public function testParseWithFileLocationRename(): void
    {
        $data = "some random text\n";
        $data .= "  CONFLICT (file location): src/Tests/Unit/AbstractTest.php renamed to src/Tests/Unit/AbstractTestCase.php in c61a64dc1de.";
        $data .= "some random text\n";

        $result = $this->parser->parse($data);
        static::assertFalse($result->completed);
        static::assertSame(['src/Tests/Unit/AbstractTestCase.php'], $result->conflicts);
    }

    public function testParseWithConflicts(): void
    {
        $data = "some random text\n";
        $data .= "  CONFLICT (modify/delete): src/Tests/Unit/AbstractTestCase.php deleted in HEAD and modified in 69270ec5834   ";
        $data .= "some random text\n";

        $result = $this->parser->parse($data);
        static::assertFalse($result->completed);
        static::assertSame(['src/Tests/Unit/AbstractTestCase.php'], $result->conflicts);
    }

    public function testParseFailedWithoutConflicts(): void
    {
        $data = "some random text\n";
        $data .= "some random text\n";

        $result = $this->parser->parse($data);
        static::assertFalse($result->completed);
        static::assertSame([], $result->conflicts);
    }
}
