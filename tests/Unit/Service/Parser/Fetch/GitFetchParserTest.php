<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser\Fetch;

use DR\Review\Entity\Git\Fetch\BranchCreation;
use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Service\Parser\Fetch\GitFetchParser;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Parser\Fetch\GitFetchParser
 */
class GitFetchParserTest extends AbstractTestCase
{
    /**
     * @covers ::parse
     */
    public function testParse(): void
    {
        $parser = new GitFetchParser();
        $result = $parser->parse(file_get_contents(__DIR__ . '/log.txt'));

        $expected = [
            new BranchCreation('NewBranch', 'origin/NewBranch'),
            new BranchUpdate('9a07ab4ebc', '060fab08bd', 'master', 'origin/master')
        ];

        static::assertEquals($expected, $result);
    }
}
