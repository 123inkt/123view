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
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files (default: skip)')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Maximum number of comments to export');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io        = new SymfonyStyle($input, $output);
        $outputDir = rtrim((string)$input->getArgument('output-dir'), '/\\');
        $force     = (bool)$input->getOption('force');
        $limitOpt  = $input->getOption('limit');
        $limit     = $limitOpt !== null ? (int)$limitOpt : null;

        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
            $io->error(sprintf('Cannot create output directory: %s', $outputDir));

            return self::FAILURE;
        }

        $total = $limit ?? $this->commentRepository->count([]);
        $io->title(sprintf('Exporting %s comment threads to %s', $limit !== null ? "up to $limit" : $total, $outputDir));
        $io->progressStart($total);

        $offset   = 0;
        $exported = 0;
        $skipped  = 0;

        while (true) {
            $batchSize = $limit !== null ? min(self::BATCH_SIZE, $limit - $exported - $skipped) : self::BATCH_SIZE;
            $comments  = $this->commentRepository->findBatch($offset, $batchSize);
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

                if ($limit !== null && ($exported + $skipped) >= $limit) {
                    break;
                }
            }

            $this->entityManager->clear();
            $offset += $batchSize;

            if (count($comments) < $batchSize || ($limit !== null && ($exported + $skipped) >= $limit)) {
                break;
            }
        }

        $io->progressFinish();
        $io->success(sprintf('Done — exported: %d, skipped (already exist): %d', $exported, $skipped));

        return self::SUCCESS;
    }
}
