<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\AzureAd;

class LoginFailure implements LoginResultInterface
{
    public function __construct(private string $message)
    {
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
