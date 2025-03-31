<?php
declare(strict_types=1);

namespace DR\Review\Service\User;

use DR\Review\Entity\User\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class IdeUrlPatternProvider
{
    public function __construct(private readonly string $ideUrlPattern, private readonly AuthorizationCheckerInterface $security)
    {
    }

    public function getUrl(): string
    {
        $user = $this->security->getUser();
        if ($user instanceof User && $user->getSetting()->getIdeUrl() !== null) {
            return $user->getSetting()->getIdeUrl();
        }

        return $this->ideUrlPattern;
    }
}
