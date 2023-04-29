<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\CodeInspection;

use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Report\CodeInspection\CodeInspectionIssueParserProvider;
use DR\Review\Service\Report\CodeInspection\CodeInspectionReportFactory;
use DR\Review\Service\Report\CodeInspection\Parser\CodeInspectionIssueParserInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeInspectionReportFactory::class)]
class CodeInspectionReportFactoryTest extends AbstractTestCase
{
    private CodeInspectionIssueParserProvider&MockObject $parserProvider;
    private CodeInspectionReportFactory                  $reportFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parserProvider = $this->createMock(CodeInspectionIssueParserProvider::class);
        $this->reportFactory  = new CodeInspectionReportFactory($this->parserProvider);
    }

    public function testParseNoIssues(): void
    {
        $repository = new Repository();

        $parser = $this->createMock(CodeInspectionIssueParserInterface::class);
        $parser->expects(self::once())->method('parse')->willReturn([]);

        $this->parserProvider->expects(self::once())->method('getParser')->with('format')->willReturn($parser);

        $report = $this->reportFactory->parse($repository, 'hash', 'inspectionId', 'format', 'basePath', 'content');
        static::assertNull($report);
    }

    public function testParse(): void
    {
        $time = time();

        $repository = new Repository();
        $issue      = new CodeInspectionIssue();

        $parser = $this->createMock(CodeInspectionIssueParserInterface::class);
        $parser->expects(self::once())->method('parse')->willReturn([$issue]);

        $this->parserProvider->expects(self::once())->method('getParser')->with('format')->willReturn($parser);

        $report = $this->reportFactory->parse($repository, 'hash', 'inspectionId', 'format', 'basePath', 'content');

        static::assertSame($repository, $report->getRepository());
        static::assertSame('inspectionId', $report->getInspectionId());
        static::assertSame('hash', $report->getCommitHash());
        static::assertGreaterThanOrEqual($time, $report->getCreateTimestamp());
        static::assertGreaterThanOrEqual([$issue], $report->getIssues()->toArray());
    }
}
