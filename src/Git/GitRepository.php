<?php
declare(strict_types=1);

namespace DR\Review\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitCommandBuilderInterface;
use DR\Review\Service\Git\SensitiveGitCommandBuilderInterface;
use Monolog\Level;
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
        private readonly LoggerInterface $gitLogger,
        private readonly Repository $repository,
        private readonly ?Stopwatch $stopWatch,
        private readonly string $repositoryPath
    ) {
    }

    /**
     * Execute a git command via CLI.
     *
     * When the builder declares requiresShell() === true the command string is passed to a shell
     * (fromShellCommandline), preserving pipe tokens.  All other commands are executed via argv
     * (new Process(array)) to avoid shell-injection and quoting issues.
     *
     * @param int|null $timeout Process timeout in seconds; null means no timeout.
     * @throws ProcessFailedException
     */
    public function execute(GitCommandBuilderInterface $commandBuilder, bool $errorOutputAsOutput = false, ?int $timeout = 300): string
    {
        $this->gitLogger->info(
            'Executing `{command}` for `{name}`',
            ['command' => (string)$commandBuilder, 'name' => $this->repository->getName()]
        );

        $action = $commandBuilder->command();

        $this->stopWatch?->start('git.' . $action, 'git');

        $redactions = $commandBuilder instanceof SensitiveGitCommandBuilderInterface
            ? $commandBuilder->getSensitiveReplacements()
            : [];

        try {
            if ($commandBuilder->requiresShell()) {
                $process = Process::fromShellCommandline(implode(' ', $commandBuilder->build()));
                $process->setTimeout($timeout);
                $process->setWorkingDirectory($this->repositoryPath);
            } else {
                $process = new Process($commandBuilder->build(), $this->repositoryPath);
                $process->setTimeout($timeout);
            }

            $process->run();
        } finally {
            $this->stopWatch?->stop('git.' . $action);
        }

        if ($process->isSuccessful() === false) {
            $this->logProcessOutput($process, false, $redactions);
            throw new ProcessFailedException($process);
        }

        $this->logProcessOutput($process, true, $redactions);

        $output = $process->getOutput();
        if ($errorOutputAsOutput === true) {
            $output .= $process->getErrorOutput();
        }

        return str_replace("\r", '', $output);
    }

    /**
     * @param array<string, string> $redactions
     */
    private function logProcessOutput(Process $process, bool $success, array $redactions = []): void
    {
        $status = $success ? 'succeeded' : 'failed';
        $level  = $success ? Level::Info : Level::Notice;
        $output = trim(str_replace("\r", '', $process->getOutput()));
        $error  = trim(str_replace("\r", '', $process->getErrorOutput()));

        // limit size
        $output = substr($output, 0, 1000);
        $error  = substr($error, 0, 1000);

        // apply redactions
        foreach ($redactions as $search => $replace) {
            $output = str_replace($search, $replace, $output);
            $error  = str_replace($search, $replace, $error);
        }

        $message = <<<MSG
Process status: {status}
Output: {output}
=========================
Error: {error}
MSG;
        $this->gitLogger->log($level, $message, ['status' => $status, 'output' => $output, 'error' => $error]);
    }
}
