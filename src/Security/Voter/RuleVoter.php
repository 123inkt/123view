<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\Voter;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RuleVoter extends Voter
{
    public const  EDIT   = 'rule.edit';
    public const  DELETE = 'rule.delete';

    private const SUPPORTED_ATTRIBUTES = [self::EDIT, self::DELETE];

    protected function supports(string $attribute, mixed $subject): bool
    {
        // only support rules, and correct attributes
        return $subject instanceof Rule && in_array($attribute, self::SUPPORTED_ATTRIBUTES, true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if ($user === null || $user instanceof User === false) {
            return false;
        }

        /** @var Rule $rule */
        $rule = $subject;

        // user must own the rule
        return $rule->getUser()?->getId() === $user->getId();
    }
}
