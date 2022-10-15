<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

use DR\GitCommitNotification\Service\Git\Branch\GitBranchCommandBuilder;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutCommandBuilder;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickCommandBuilder;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilder;
use DR\GitCommitNotification\Service\Git\Reset\GitResetCommandBuilder;
use DR\GitCommitNotification\Service\Git\Show\GitShowCommandBuilder;

class GitCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function createShow(): GitShowCommandBuilder
    {
        return new GitShowCommandBuilder($this->git);
    }

    public function createDiff(): GitDiffCommandBuilder
    {
        return new GitDiffCommandBuilder($this->git);
    }

    public function createLog(): GitLogCommandBuilder
    {
        return new GitLogCommandBuilder($this->git);
    }

    public function createCheryPick(): GitCherryPickCommandBuilder
    {
        return new GitCherryPickCommandBuilder($this->git);
    }

    public function createCheckout(): GitCheckoutCommandBuilder
    {
        return new GitCheckoutCommandBuilder($this->git);
    }

    public function createBranch(): GitBranchCommandBuilder
    {
        return new GitBranchCommandBuilder($this->git);
    }

    public function createReset(): GitResetCommandBuilder
    {
        return new GitResetCommandBuilder($this->git);
    }
}
