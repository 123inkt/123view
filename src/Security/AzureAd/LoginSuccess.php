<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\AzureAd;

class LoginSuccess implements LoginResultInterface
{
    public function __construct(private string $name, private string $email)
    {
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
