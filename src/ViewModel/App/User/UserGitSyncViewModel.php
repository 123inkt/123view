<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\User;

class UserGitSyncViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly bool $gitlabSyncEnabled, public readonly bool $hasGitlabToken)
    {
    }
}
