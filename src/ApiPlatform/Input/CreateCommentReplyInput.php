<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Input;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTagEnum;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCommentReplyInput
{
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(max: Comment::MAX_COMMENT_LENGTH)]
    public string $message;

    public ?CommentTagEnum $tag = null;
}
