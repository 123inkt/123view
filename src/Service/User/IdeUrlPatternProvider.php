<?php
declare(strict_types=1);

namespace DR\Review\Service\User;

use DR\Review\Entity\User\User;

class IdeUrlPatternProvider
{
    public function __construct(private readonly string $ideUrlPattern, private readonly UserEntityProvider $userEntityProvider)
    {
    }

    public function getUrl(): string
    {
        $user = $this->userEntityProvider->getUser();
        if ($user instanceof User && $user->getSetting()->getIdeUrl() !== null) {
            return $user->getSetting()->getIdeUrl();
        }

        return $this->ideUrlPattern;
    }
}
