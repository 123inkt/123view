<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Input;

use DR\Review\Entity\Review\CommentTagEnum;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCommentInput
{
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(max: 2000)]
    public string $message;

    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(max: 500)]
    public string $filepath;

    /** @var positive-int */
    #[Assert\Positive]
    public int $line;

    public ?CommentTagEnum $tag = null;
}
