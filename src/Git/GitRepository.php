<?php
declare(strict_types=1);

namespace DR\Review\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitCommandBuilderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @codeCoverageIgnore
 */
class GitRepository
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Repository $repository,
        private readonly ?StopWatch $stopWatch,
        private readonly string $repositoryPath
    ) {
    }

    /**
     * Execute git command via cli
     * Note: Using Symfony's Process to avoid shell-escape argument issues with GitRepository::execute method.
     * @throws ProcessFailedException
     */
    public function execute(GitCommandBuilderInterface $commandBuilder, bool $errorOutputAsOutput = false): string
    {
        $this->logger->info('Executing `{command}` for `{name}`', ['command' => (string)$commandBuilder, 'name' => $this->repository->getName()]);

        $action = $commandBuilder->command();

        $this->stopWatch?->start('git.' . $action, 'git');
        try {
            $process = Process::fromShellCommandline(implode(' ', $commandBuilder->build()));
            $process->setTimeout(300);
            $process->setWorkingDirectory($this->repositoryPath);
            $process->run();
        } finally {
            $this->stopWatch?->stop('git.' . $action);
        }

        // executes after the command finishes
        if ($process->isSuccessful() === false) {
            $this->logProcessOutput($process, false);
            throw new ProcessFailedException($process);
        }

        $this->logProcessOutput($process, true);

        $output = $process->getOutput();
        if ($errorOutputAsOutput === true) {
            $output .= $process->getErrorOutput();
        }

        // remove any \r in the output
        return str_replace("\r", "", $output);
    }

    private function logProcessOutput(Process $process, bool $success): void
    {
        $status = $success ? 'succeeded' : 'failed';
        $output = trim(str_replace("\r", "", $process->getOutput()));
        $error  = trim(str_replace("\r", "", $process->getErrorOutput()));

        if ($error !== '') {
            $this->logger->debug('Process {status}: error: {error}', ['status' => $status, 'error' => $error]);
        }
        if ($output !== '') {
            $this->logger->debug('Process {status}: output: {output}', ['status' => $status, 'output' => $output]);
        }
    }
}
