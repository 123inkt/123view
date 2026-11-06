<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Service\Git\GitProcessExceptionInspector;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[CoversClass(GitProcessExceptionInspector::class)]
class GitProcessExceptionInspectorTest extends AbstractTestCase
{
    #[DataProvider('provideGitFailures')]
    public function testIsMissingRevision(int $exitCode, string $errorOutput, bool $expected): void
    {
        $process = static::createStub(Process::class);
        $process->method('getExitCode')->willReturn($exitCode);
        $process->method('getErrorOutput')->willReturn($errorOutput);
        $exception = new ProcessFailedException($process);

        static::assertSame($expected, new GitProcessExceptionInspector()->isMissingRevision($exception));
    }

    /**
     * @return array<string, array{int, string, bool}>
     */
    public static function provideGitFailures(): array
    {
        return [
            'missing revision' => [
                128,
                "fatal: ambiguous argument 'origin/main...origin/feature': unknown revision or path not in the working tree.",
                true,
            ],
            'reported missing release revision' => [
                128,
                "fatal: ambiguous argument 'origin/2026.45.7..."
                . "origin/B319783_TP_OrderReference_contains_wrong_value-2026.45': unknown revision or path not in the working tree.\n"
                . "Use '--' to separate paths from revisions, like this:\n"
                . "'git <command> [<revision>...] -- [<file>...]'\n",
                true,
            ],
            'repository not found'    => [128, 'fatal: not a git repository (or any of the parent directories): .git', false],
            'permission denied'      => [128, 'fatal: could not read from remote repository.', false],
            'different exit code'    => [1, "fatal: ambiguous argument 'range': unknown revision or path not in the working tree.", false],
            'ambiguous path'         => [128, "fatal: ambiguous argument 'range': both revision and filename", false],
            'different error format' => [128, 'unknown revision or path not in the working tree.', false],
        ];
    }
}
