<?php
declare(strict_types=1);

namespace DR\Review\Security\Webhook;

use Symfony\Component\Security\Core\User\UserInterface;

class WebhookUser implements UserInterface
{
    /**
     * @param string[] $roles
     */
    public function __construct(private readonly string $userIdentifier, private readonly array $roles)
    {
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
        // nothing
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
