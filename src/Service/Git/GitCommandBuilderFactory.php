<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Service\Git\Add\GitAddCommandBuilder;
use DR\Review\Service\Git\Branch\GitBranchCommandBuilder;
use DR\Review\Service\Git\Checkout\GitCheckoutCommandBuilder;
use DR\Review\Service\Git\CherryPick\GitCherryPickCommandBuilder;
use DR\Review\Service\Git\Clean\GitCleanCommandBuilder;
use DR\Review\Service\Git\Commit\GitCommitCommandBuilder;
use DR\Review\Service\Git\Diff\GitDiffCommandBuilder;
use DR\Review\Service\Git\DiffTree\GitDiffTreeCommandBuilder;
use DR\Review\Service\Git\Fetch\GitFetchCommandBuilder;
use DR\Review\Service\Git\GarbageCollect\GitGarbageCollectCommandBuilder;
use DR\Review\Service\Git\Grep\GitGrepCommandBuilder;
use DR\Review\Service\Git\Log\GitLogCommandBuilder;
use DR\Review\Service\Git\LsTree\GitLsTreeCommandBuilder;
use DR\Review\Service\Git\Remote\GitRemoteCommandBuilder;
use DR\Review\Service\Git\Reset\GitResetCommandBuilder;
use DR\Review\Service\Git\RevList\GitRevListCommandBuilder;
use DR\Review\Service\Git\Show\GitShowCommandBuilder;
use DR\Review\Service\Git\Status\GitStatusCommandBuilder;

class GitCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function createAdd(): GitAddCommandBuilder
    {
        return new GitAddCommandBuilder($this->git);
    }

    public function createShow(): GitShowCommandBuilder
    {
        return new GitShowCommandBuilder($this->git);
    }

    public function createDiff(): GitDiffCommandBuilder
    {
        return new GitDiffCommandBuilder($this->git);
    }

    public function createDiffTree(): GitDiffTreeCommandBuilder
    {
        return new GitDiffTreeCommandBuilder($this->git);
    }

    public function createLog(): GitLogCommandBuilder
    {
        return new GitLogCommandBuilder($this->git);
    }

    public function createFetch(): GitFetchCommandBuilder
    {
        return new GitFetchCommandBuilder($this->git);
    }

    public function createCherryPick(): GitCherryPickCommandBuilder
    {
        return new GitCherryPickCommandBuilder($this->git);
    }

    public function createCheckout(): GitCheckoutCommandBuilder
    {
        return new GitCheckoutCommandBuilder($this->git);
    }

    public function createCommit(): GitCommitCommandBuilder
    {
        return new GitCommitCommandBuilder($this->git);
    }

    public function createBranch(): GitBranchCommandBuilder
    {
        return new GitBranchCommandBuilder($this->git);
    }

    public function createReset(): GitResetCommandBuilder
    {
        return new GitResetCommandBuilder($this->git);
    }

    public function createRemote(): GitRemoteCommandBuilder
    {
        return new GitRemoteCommandBuilder($this->git);
    }

    public function createStatus(): GitStatusCommandBuilder
    {
        return new GitStatusCommandBuilder($this->git);
    }

    public function createLsTree(): GitLsTreeCommandBuilder
    {
        return new GitLsTreeCommandBuilder($this->git);
    }

    public function createGrep(): GitGrepCommandBuilder
    {
        return new GitGrepCommandBuilder($this->git);
    }

    public function createRevList(): GitRevListCommandBuilder
    {
        return new GitRevListCommandBuilder($this->git);
    }

    public function createClean(): GitCleanCommandBuilder
    {
        return new GitCleanCommandBuilder($this->git);
    }

    public function createGarbageCollect(): GitGarbageCollectCommandBuilder
    {
        return new GitGarbageCollectCommandBuilder($this->git);
    }
}
