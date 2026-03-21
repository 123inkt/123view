<?php
declare(strict_types=1);

namespace DR\Review\Security\Voter;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
    public const  EDIT   = 'comment.edit';
    public const  DELETE = 'comment.delete';

    private const SUPPORTED_ATTRIBUTES = [self::EDIT, self::DELETE];

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // only support comments, and correct attributes
        return $subject instanceof Comment && in_array($attribute, self::SUPPORTED_ATTRIBUTES, true);
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

        /** @var Comment $comment */
        $comment = $subject;

        // user must own the comment
        return $comment->getUser()->getId() === $user->getId();
    }
}
