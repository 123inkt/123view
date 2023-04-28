<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\Coverage;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Report\Coverage\CoberturaParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CoberturaParser::class)]
class CoberturaParserTest extends AbstractTestCase
{
    public function testParse(): void
    {
        $data = file_get_contents(__DIR__ . '/../../../../../coverage-coburtara.xml');

        $coberturaParser = new CoberturaParser();
        $coverage        = $coberturaParser->parse(new Repository(), $data);
    }
}
