<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Report;

use DR\Review\Command\Report\CodeCoverageCleanUpCommand;
use DR\Review\Repository\Report\CodeCoverageReportRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CodeCoverageCleanUpCommand::class)]
class CodeCoverageCleanUpCommandTest extends AbstractTestCase
{
    private CodeCoverageReportRepository&MockObject $reportRepository;
    private CodeCoverageCleanUpCommand              $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportRepository = $this->createMock(CodeCoverageReportRepository::class);
        $this->command          = new CodeCoverageCleanUpCommand($this->reportRepository);
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
