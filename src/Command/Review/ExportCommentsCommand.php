<?php
declare(strict_types=1);

namespace DR\Review\Command\Review;

use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('review:export-comments', 'Export all comment threads to markdown files')]
class ExportCommentsCommand extends Command
{
    private const BATCH_SIZE = 500;

    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly CommentExportService $exportService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('output-dir', InputArgument::REQUIRED, 'Directory to write markdown files to')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files (default: skip)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io        = new SymfonyStyle($input, $output);
        $outputDir = rtrim((string)$input->getArgument('output-dir'), '/\\');
        $force     = (bool)$input->getOption('force');

        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
            $io->error(sprintf('Cannot create output directory: %s', $outputDir));

            return self::FAILURE;
        }

        $total = $this->commentRepository->count([]);
        $io->title(sprintf('Exporting %d comment threads to %s', $total, $outputDir));
        $io->progressStart($total);

        $offset   = 0;
        $exported = 0;
        $skipped  = 0;

        while (true) {
            $comments = $this->commentRepository->findBatch($offset, self::BATCH_SIZE);
            if ($comments === []) {
                break;
            }

            foreach ($comments as $comment) {
                $filePath = $outputDir . DIRECTORY_SEPARATOR . $comment->getId() . '.md';

                if (!$force && file_exists($filePath)) {
                    ++$skipped;
                    $io->progressAdvance();
                    continue;
                }

                $markdown = $this->exportService->generateMarkdown($comment);
                file_put_contents($filePath, $markdown);
                ++$exported;
                $io->progressAdvance();
            }

            $this->entityManager->clear();
            $offset += self::BATCH_SIZE;

            if (count($comments) < self::BATCH_SIZE) {
                break;
            }
        }

        $io->progressFinish();
        $io->success(sprintf('Done — exported: %d, skipped (already exist): %d', $exported, $skipped));

        return self::SUCCESS;
    }
}
