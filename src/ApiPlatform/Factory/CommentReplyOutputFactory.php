<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Factory;

use DateTimeImmutable;
use DR\Review\ApiPlatform\Output\CommentReplyOutput;
use DR\Review\Entity\Review\CommentReply;

class CommentReplyOutputFactory
{
    public function create(CommentReply $reply): CommentReplyOutput
    {
        return new CommentReplyOutput(
            $reply->getId(),
            $reply->getComment()->getId(),
            $reply->getUser()->getId(),
            $reply->getMessage(),
            $reply->getTag()?->value,
            new DateTimeImmutable()->setTimestamp($reply->getCreateTimestamp()),
            new DateTimeImmutable()->setTimestamp($reply->getUpdateTimestamp()),
        );
    }
}
