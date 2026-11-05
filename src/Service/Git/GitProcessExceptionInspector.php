<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use Symfony\Component\Process\Exception\ProcessFailedException;

class GitProcessExceptionInspector
{
    public function isMissingRevision(ProcessFailedException $exception): bool
    {
        $process = $exception->getProcess();

        return $process->getExitCode() === 128
            && str_contains($process->getErrorOutput(), 'fatal: ambiguous argument ')
            && str_contains($process->getErrorOutput(), 'unknown revision or path not in the working tree.');
    }
}
