<?php
declare(strict_types=1);

namespace DR\Review\Security;

use DR\Review\Security\Role\Roles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (in_array(Roles::ROLE_BANNED, $user->getRoles(), true)) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('user.account.suspended'));
        }
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        // intentionally unused
    }
}
