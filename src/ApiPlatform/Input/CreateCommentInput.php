<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Input;

use DR\Review\Entity\Review\CommentTagEnum;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCommentInput
{
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Type('string')]
    #[Assert\Length(max: 2000)]
    public string $message;

    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Type('string')]
    #[Assert\Length(max: 500)]
    public string $filepath;

    /** @var positive-int */
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $line;

    #[Assert\Type('string')]
    #[Assert\Choice(choices: [
        CommentTagEnum::Suggestion->value,
        CommentTagEnum::NiceToHave->value,
        CommentTagEnum::ChangeRequest->value,
        CommentTagEnum::Explanation->value,
    ])]
    public ?string $tag = null;
}
