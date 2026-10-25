<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Factory;

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
            $reply->getCreateTimestamp(),
            $reply->getUpdateTimestamp(),
        );
    }
}
