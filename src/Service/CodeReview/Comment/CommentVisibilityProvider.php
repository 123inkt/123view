<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CommentVisibility;
use DR\Review\Security\SessionKeys;
use Symfony\Component\HttpFoundation\RequestStack;

class CommentVisibilityProvider
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getCommentVisibility(): CommentVisibility
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return CommentVisibility::ALL;
        }

        $value = $request->getSession()->get(SessionKeys::REVIEW_COMMENT_VISIBILITY->value);
        if ($value === null) {
            return CommentVisibility::ALL;
        }

        return CommentVisibility::tryFrom($value) ?? CommentVisibility::ALL;
    }
}
