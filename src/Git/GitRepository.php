<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Git;

use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @codeCoverageIgnore
 */
class GitRepository
{
    private string $repositoryPath;

    public function __construct(string $repositoryPath)
    {
        $this->repositoryPath = $repositoryPath;
    }

    /**
     * Get the git commit log for the given repository.
     * Note: Using Symfony's Process to avoid shell-escape argument issues with GitRepository::execute method.
     */
    public function execute(GitCommandBuilderInterface $commandBuilder): string
    {
        $process = Process::fromShellCommandline(implode(' ', $commandBuilder->build()));
        $process->setWorkingDirectory($this->repositoryPath);
        $process->run();

        // executes after the command finishes
        if ($process->isSuccessful() === false) {
            throw new ProcessFailedException($process);
        }

        // remove any \r in the output
        return str_replace("\r", "", $process->getOutput());
    }
}
