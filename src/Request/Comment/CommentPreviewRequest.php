<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class CommentPreviewRequest extends AbstractValidatedRequest
{
    public function getMessage(): string
    {
        return (string)$this->request->query->get('message');
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['query' => ['message' => 'required|string|filled|max:2000']]);
    }
}
