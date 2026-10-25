<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

interface GitCommandBuilderInterface
{
    public function command(): string;

    /**
     * @return string[]
     */
    public function build(): array;

    /**
     * Returns true when the builder produces a shell expression that must be executed via a shell
     * (e.g. when a pipe is present).  Argv-safe builders return false.
     */
    public function requiresShell(): bool;

    public function __toString(): string;
}
