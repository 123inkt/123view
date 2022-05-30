<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Lib\AzureAd;

use DR\GitCommitNotification\Entity\User;

class LoginSuccess implements LoginResultInterface
{
    public function __construct(private User $user)
    {
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
