<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use Generator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class RipGrepProcessExecutor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param string[] $arguments
     *
     * @return Generator<string>
     */
    public function execute(array $arguments, string $cwd): Generator
    {
        $commandLine = '/usr/bin/rg ' . implode(' ', array_map('escapeshellarg', $arguments));

        $this->logger?->info('Executing command `{command}`', ['command' => $commandLine]);

        // change working directory
        $workingDir = getcwd();
        chdir($cwd);

        $exitCode = 1;
        $handle   = popen($commandLine, 'r');

        // restore working directory
        chdir($workingDir);

        if (is_resource($handle)) {
            while (feof($handle) === false) {
                $line = fgets($handle);
                if ($line === false) {
                    break;
                }
                yield $line;
            }

            $exitCode = pclose($handle);
        }

        $this->logger?->info('Command exited with exit code {code}', ['code' => $exitCode]);
    }
}
