<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\Voter;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentReplyVoter extends Voter
{
    public const  EDIT   = 'reply.edit';
    public const  DELETE = 'reply.delete';

    private const SUPPORTED_ATTRIBUTES = [self::EDIT, self::DELETE];

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // only support replies, and correct attributes
        return $subject instanceof CommentReply && in_array($attribute, self::SUPPORTED_ATTRIBUTES, true);
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if ($user === null || $user instanceof User === false) {
            return false;
        }

        /** @var CommentReply $reply */
        $reply = $subject;

        // user must own the comment
        return $reply->getUser()?->getId() === $user->getId();
    }
}
