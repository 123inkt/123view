<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class CommentVisibilityRequest extends AbstractValidatedRequest
{
    public function getVisibility(): string
    {
        return $this->request->request->get('visibility', '');
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['request' => ['visibility' => 'required|string|in:all,unresolved,none']]);
    }
}
