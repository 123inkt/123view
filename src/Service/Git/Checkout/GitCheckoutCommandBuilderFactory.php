<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Checkout;

class GitCheckoutCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function create(): GitCheckoutCommandBuilder
    {
        return new GitCheckoutCommandBuilder($this->git);
    }
}
