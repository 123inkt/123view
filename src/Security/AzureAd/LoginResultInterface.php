<?php
declare(strict_types=1);

namespace DR\Review\Security\AzureAd;

interface LoginResultInterface
{
    public function isSuccess(): bool;
}
