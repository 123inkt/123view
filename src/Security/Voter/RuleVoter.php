<?php
declare(strict_types=1);

namespace DR\Review\Security\Voter;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RuleVoter extends Voter
{
    public const  EDIT   = 'rule.edit';
    public const  DELETE = 'rule.delete';

    private const SUPPORTED_ATTRIBUTES = [self::EDIT, self::DELETE];

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // only support rules, and correct attributes
        return $subject instanceof Rule && in_array($attribute, self::SUPPORTED_ATTRIBUTES, true);
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if ($user === null || $user instanceof User === false) {
            return false;
        }

        /** @var Rule $rule */
        $rule = $subject;

        // user must own the rule
        return $rule->getUser()->getId() === $user->getId();
    }
}
