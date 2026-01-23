<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Security\SessionKeys;
use Symfony\Component\HttpFoundation\RequestStack;

class CommentVisibilityProvider
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getCommentVisibility(): CommentVisibilityEnum
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return CommentVisibilityEnum::ALL;
        }

        $value = $request->getSession()->get(SessionKeys::REVIEW_COMMENT_VISIBILITY->value);
        if (is_string($value) === false) {
            return CommentVisibilityEnum::ALL;
        }

        return CommentVisibilityEnum::tryFrom($value) ?? CommentVisibilityEnum::ALL;
    }
}
