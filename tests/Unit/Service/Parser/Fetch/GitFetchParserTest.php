<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser\Fetch;

use DR\Review\Service\Parser\Fetch\GitFetchParser;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Parser\Fetch\GitFetchParser
 * @covers ::__construct
 */
class GitFetchParserTest extends AbstractTestCase
{
    /**
     * @covers ::parse
     */
    public function testParse(): void
    {
        $parser = new GitFetchParser();
        $parser->parse(file_get_contents(__DIR__ . '/log.txt'));
    }
}
