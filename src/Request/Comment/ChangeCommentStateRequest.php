<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Entity\Review\CommentStateEnum;

class ChangeCommentStateRequest extends AbstractValidatedRequest
{
    public function getState(): CommentStateEnum
    {
        return CommentStateEnum::from($this->request->request->getString('state'));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'request' => ['state' => 'required|string|in:' . implode(',', CommentStateEnum::values())]
            ]
        );
    }
}
