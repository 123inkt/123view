<?php
declare(strict_types=1);

namespace DR\Review\Command\Report;

use DR\Review\Repository\Report\CodeInspectionReportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('code-inspection:cleanup', "Clean up all code inspection reports from 2 weeks or older")]
class CodeInspectionCleanUpCommand extends Command
{
    public function __construct(private readonly CodeInspectionReportRepository $reportRepository)
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $removed = $this->reportRepository->cleanUp(strtotime("-2 weeks"));

        $output->writeln("Removed " . $removed . " reports");

        return self::SUCCESS;
    }
}
