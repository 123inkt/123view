<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Lib\AzureAd;

interface LoginResultInterface
{
    public function isSuccess(): bool;
}
