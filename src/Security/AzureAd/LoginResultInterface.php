<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\AzureAd;

interface LoginResultInterface
{
    public function isSuccess(): bool;
}
