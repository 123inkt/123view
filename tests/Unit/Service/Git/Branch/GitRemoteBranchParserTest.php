<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Branch;

use DR\Review\Service\Git\Branch\GitRemoteBranchParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitRemoteBranchParser::class)]
class GitRemoteBranchParserTest extends AbstractTestCase
{
    public function testParse(): void
    {
        $output = "  origin/branch-1\n";
        $output .= "  origin/branch-2\n";
        $output .= "  origin/HEAD -> origin/master\n";
        $output .= "  origin/master\n";

        $expected = [
            'origin/branch-1',
            'origin/branch-2',
            'origin/HEAD',
            'origin/master'
        ];

        $parser   = new GitRemoteBranchParser();
        $branches = $parser->parse($output);

        static::assertSame($expected, $branches);
    }
}
