<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Entity\Review\CommentVisibilityEnum;

class CommentVisibilityRequest extends AbstractValidatedRequest
{
    public function getVisibility(): CommentVisibilityEnum
    {
        return CommentVisibilityEnum::from((string)$this->request->request->get('visibility'));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['request' => ['visibility' => 'required|string|in:' . implode(',', CommentVisibilityEnum::values())]]);
    }
}
