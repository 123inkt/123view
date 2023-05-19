<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\RevList;

use DR\Review\Service\Git\RevList\GitRevListParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitRevListParser::class)]
class GitRevListParserTest extends AbstractTestCase
{
    private GitRevListParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new GitRevListParser();
    }

    public function testParseOneLineNoMatches(): void
    {
        $data = "Commit message 1\n";
        $data .= "<474fc24e822336664f19f50c6da44617027137ea Commit message 2\n";

        $hashes = $this->parser->parseOneLine($data);
        static::assertSame([], $hashes);
    }

    public function testParseOneLine(): void
    {
        $data = ">37b9d66ca81aa8bb8a48d4d555e42a366701fa4a Commit message 1\n";
        $data .= ">474fc24e822336664f19f50c6da44617027137ea Commit message 2\n";
        $data .= ">c125d8bc5c7b83d2a23491221f83c09c2cae3b1c Commit message 3\n";

        $expected = [
            "37b9d66ca81aa8bb8a48d4d555e42a366701fa4a",
            "474fc24e822336664f19f50c6da44617027137ea",
            "c125d8bc5c7b83d2a23491221f83c09c2cae3b1c",
        ];

        $hashes = $this->parser->parseOneLine($data);
        static::assertSame($expected, $hashes);
    }
}
