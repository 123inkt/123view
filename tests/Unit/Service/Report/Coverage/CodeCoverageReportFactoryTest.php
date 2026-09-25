<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\Coverage;

use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Report\Coverage\CodeCoverageParserProvider;
use DR\Review\Service\Report\Coverage\CodeCoverageReportFactory;
use DR\Review\Service\Report\Coverage\Parser\CodeCoverageParserInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeCoverageReportFactory::class)]
class CodeCoverageReportFactoryTest extends AbstractTestCase
{
    private CodeCoverageParserProvider&MockObject $parserProvider;
    private CodeCoverageReportFactory             $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parserProvider = $this->createMock(CodeCoverageParserProvider::class);
        $this->factory        = new CodeCoverageReportFactory($this->parserProvider);
    }

    public function testParse(): void
    {
        $repository = new Repository();
        $file       = new CodeCoverageFile();
        $parser     = $this->createMock(CodeCoverageParserInterface::class);

        $this->parserProvider->expects($this->once())->method('getParser')->with('format')->willReturn($parser);
        $parser->expects($this->once())->method('parse')->with('basePath', 'data')->willReturn([$file]);

        $report = $this->factory->parse($repository, 'commitHash', 'branchId', 'format', 'basePath', 'data');
        static::assertSame($repository, $report->getRepository());
        static::assertSame('commitHash', $report->getCommitHash());
        static::assertSame('branchId', $report->getBranchId());
        static::assertEqualsWithDelta(time(), $report->getCreateTimestamp(), 10);
        static::assertSame([$file], $report->getFiles()->toArray());
    }
}
