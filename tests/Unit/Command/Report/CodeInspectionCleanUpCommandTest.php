<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Report;

use DR\Review\Command\Report\CodeInspectionCleanUpCommand;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CodeInspectionCleanUpCommand::class)]
class CodeInspectionCleanUpCommandTest extends AbstractTestCase
{
    private CodeInspectionReportRepository&MockObject $reportRepository;
    private CodeInspectionCleanUpCommand              $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportRepository = $this->createMock(CodeInspectionReportRepository::class);
        $this->command          = new CodeInspectionCleanUpCommand($this->reportRepository);
    }

    public function testExecute(): void
    {
        $this->reportRepository->expects($this->once())->method('cleanUp')->willReturn(5);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertSame("Removed 5 reports", trim($tester->getDisplay()));
    }
}
