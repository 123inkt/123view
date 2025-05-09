<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Service\Search\RipGrep\Iterator\ProcessOutputIterator;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class RipGrepProcessExecutor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param string[] $arguments
     */
    public function execute(array $arguments, string $cwd): ProcessOutputIterator
    {
        $commandLine = '/usr/bin/rg ' . implode(' ', array_map('escapeshellarg', $arguments));

        $this->logger?->info('Executing command `{command}`', ['command' => $commandLine]);

        // change working directory
        $workingDir = getcwd();
        chdir($cwd);

        $exitCode = 1;
        $handle   = Assert::notFalse(popen($commandLine, 'r'), 'Failed to open process for command: ' . $commandLine);

        // restore working directory
        chdir($workingDir);

        return new ProcessOutputIterator($handle);
    }
}
