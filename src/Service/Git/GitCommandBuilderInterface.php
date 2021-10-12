<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

interface GitCommandBuilderInterface
{
    /**
     * @return string[]
     */
    public function build(): array;

    public function __toString(): string;
}
