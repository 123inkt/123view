<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Input;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\Review\CommentTagEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpdateCommentInput
{
    #[Assert\When('this.hasMessage()', new Assert\NotBlank(normalizer: 'trim'))]
    #[Assert\Length(max: Comment::MAX_COMMENT_LENGTH)]
    public string           $message;
    public CommentStateEnum $state;
    private bool            $tagProvided = false;
    private ?CommentTagEnum $tag;

    public function hasMessage(): bool
    {
        return isset($this->message);
    }

    public function setTag(?CommentTagEnum $tag): self
    {
        $this->tagProvided = true;
        $this->tag         = $tag;

        return $this;
    }

    public function getTag(): ?CommentTagEnum
    {
        return $this->tag;
    }

    public function hasTag(): bool
    {
        return $this->tagProvided;
    }

    public function hasState(): bool
    {
        return isset($this->state);
    }

    public function hasChanges(): bool
    {
        return isset($this->message) || isset($this->state) || $this->tagProvided;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->hasChanges() === false) {
            $context->buildViolation('At least one of message, tag, or state must be provided.')->addViolation();
        }
    }
}
