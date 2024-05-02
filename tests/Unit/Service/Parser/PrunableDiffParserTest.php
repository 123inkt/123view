<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser;

use DR\Review\Service\Parser\PrunableDiffParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PrunableDiffParser::class)]
class PrunableDiffParserTest extends AbstractTestCase
{
    public function testParse(): void
    {
    }
}
