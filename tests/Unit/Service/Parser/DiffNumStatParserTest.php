<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionFile;
use DR\Review\Service\Parser\DiffNumStatParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffNumStatParser::class)]
class DiffNumStatParserTest extends AbstractTestCase
{
    private DiffNumStatParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DiffNumStatParser();
    }

    public function testParse(): void
    {
        $revision = new Revision();
        $output = <<<OUTPUT
85      80      src/composer.lock
5       0       src/Service/Service.ts
0       2       src/Provider/Provider.php
foobar
OUTPUT;

        $expected = [
            (new RevisionFile())->setRevision($revision)->setLinesAdded(85)->setLinesRemoved(80)->setFilepath('src/composer.lock'),
            (new RevisionFile())->setRevision($revision)->setLinesAdded(5)->setLinesRemoved(0)->setFilepath('src/Service/Service.ts'),
            (new RevisionFile())->setRevision($revision)->setLinesAdded(0)->setLinesRemoved(2)->setFilepath('src/Provider/Provider.php')
        ];
        $result = $this->parser->parse($revision, $output);
        static::assertEquals($expected, $result);
    }
}
