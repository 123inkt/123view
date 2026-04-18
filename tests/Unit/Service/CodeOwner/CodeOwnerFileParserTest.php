<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Review\Service\CodeOwner\CodeOwnerFileParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeOwnerFileParser::class)]
class CodeOwnerFileParserTest extends AbstractTestCase
{
    public function testParse(): void
    {
        $content = <<<EOT
# CODEOWNERS file

# single owner, leading/trailing whitespace
  *.js  @frontend-team

# multiple owners
*.ts @frontend-team @lead-dev

# inline comment is stripped
src/api/ @backend-team # only backend

# pattern without owners is skipped
src/ignored/

EOT;
        $expected = [
            new OwnerPattern('*.js', ['@frontend-team']),
            new OwnerPattern('*.ts', ['@frontend-team', '@lead-dev']),
            new OwnerPattern('src/api/', ['@backend-team']),
        ];

        static::assertEquals($expected, new CodeOwnerFileParser()->parse($content));
    }
}
