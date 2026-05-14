<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Review\Service\CodeOwner\CodeOwnerFileParser;
use DR\Review\Service\CodeOwner\CodeOwnerLineParser;
use DR\Review\Service\CodeOwner\CodeOwnerSectionHeaderParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class CodeOwnerFileParserTest extends AbstractTestCase
{
    private CodeOwnerFileParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CodeOwnerFileParser(new CodeOwnerLineParser(), new CodeOwnerSectionHeaderParser());
    }

    public function testParseBlockWithHeaderOwners(): void
    {
        $content = $this->getFileContents('block-with-header-owners.txt');
        $expected = [
            new OwnerPattern('src/single.php', ['@role/foo']),
            new OwnerPattern('src/multi.php', ['@role/bar', '@role/baz']),
            new OwnerPattern('src/inherited.php', ['@role/default', '@role/secondary']),
        ];

        static::assertEquals($expected, $this->parser->parse($content));
    }
}
