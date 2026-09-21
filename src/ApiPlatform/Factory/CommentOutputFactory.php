<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Factory;

use DateTimeImmutable;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\Entity\Review\Comment;

class CommentOutputFactory
{
    public function create(Comment $comment): CommentOutput
    {
        $lineReference = $comment->getLineReference();

        return new CommentOutput(
            $comment->getId(),
            $comment->getUser()->getId(),
            $comment->getReview()->getId(),
            $comment->getMessage(),
            $comment->getFilePath(),
            $lineReference->lineAfter,
            $lineReference->headSha,
            $comment->getState()->value,
            $comment->getTag()?->value,
            new DateTimeImmutable()->setTimestamp($comment->getCreateTimestamp()),
            new DateTimeImmutable()->setTimestamp($comment->getUpdateTimestamp()),
        );
    }
}
