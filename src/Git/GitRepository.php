<?php
declare(strict_types=1);

namespace DR\Review\Git;

use DR\Review\Service\Git\GitCommandBuilderInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @codeCoverageIgnore
 */
class GitRepository
{
    public function __construct(private readonly ?StopWatch $stopWatch, private readonly string $repositoryPath)
    {
    }

    /**
     * Execute git command via cli
     * Note: Using Symfony's Process to avoid shell-escape argument issues with GitRepository::execute method.
     */
    public function execute(string|GitCommandBuilderInterface $commandBuilder, bool $errorOutputAsOutput = false): string
    {
        $command = is_string($commandBuilder) ? $commandBuilder : implode(' ', $commandBuilder->build());
        $action  = is_string($commandBuilder) ? 'manual' : $commandBuilder->command();

        $this->stopWatch?->start('git.' . $action, 'git');
        try {
            $process = Process::fromShellCommandline($command);
            $process->setWorkingDirectory($this->repositoryPath);
            $process->run();
        } finally {
            $this->stopWatch?->stop('git.' . $action);
        }

        // executes after the command finishes
        if ($process->isSuccessful() === false) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        if ($errorOutputAsOutput === true) {
            $output .= $process->getErrorOutput();
        }

        // remove any \r in the output
        return str_replace("\r", "", $output);
    }
}
