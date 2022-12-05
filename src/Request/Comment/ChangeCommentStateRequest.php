<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Doctrine\Type\CommentStateType;

class ChangeCommentStateRequest extends AbstractValidatedRequest
{
    public function getState(): string
    {
        return (string)$this->request->request->get('state');
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'request' => ['state' => 'required|string|in:' . implode(',', CommentStateType::VALUES)]
            ]
        );
    }
}
