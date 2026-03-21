<?php
declare(strict_types=1);

namespace DR\Review\Security\Voter;

use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserAccessTokenVoter extends Voter
{
    public const  DELETE = 'access.token.delete';

    private const SUPPORTED_ATTRIBUTES = [self::DELETE];

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // only support access tokens, and correct attributes
        return $subject instanceof UserAccessToken && in_array($attribute, self::SUPPORTED_ATTRIBUTES, true);
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

        /** @var UserAccessToken $token */
        $token = $subject;

        // user must own the token
        return $token->getUser()->getId() === $user->getId();
    }
}
