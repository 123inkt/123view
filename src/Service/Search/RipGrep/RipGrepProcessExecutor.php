<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Model\Search\RipGrep\RipGrepResult;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class RipGrepProcessExecutor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param string[] $arguments
     */
    public function execute(array $arguments, string $cwd): RipGrepResult
    {
        $commandLine = '/usr/bin/rg ' . implode(' ', array_map('escapeshellarg', $arguments));

        $this->logger?->info('Executing command `{command}`', ['command' => $commandLine]);

        // change working directory
        $workingDir = getcwd();
        chdir($cwd);

        // Symfony Process doesn't seem to wait for the process to finish and returns no output.
        $output = system($commandLine, $exitCode);

        // restore working directory
        chdir($workingDir);

        $this->logger?->info('Command exited with exit code {code}', ['code' => $exitCode]);

        return new RipGrepResult($output, $exitCode);
    }
}
