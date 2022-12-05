<?php
declare(strict_types=1);

namespace DR\Review\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class FileSeenStatusRequest extends AbstractValidatedRequest
{
    public function getFilePath(): string
    {
        return (string)$this->request->request->get('filePath');
    }

    public function getSeenStatus(): bool
    {
        return (bool)$this->request->request->getInt('seen');
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'request' => [
                    'filePath' => 'required|string|filled|max:500',
                    'seen'     => 'required|integer|between:0,1'
                ]
            ]
        );
    }
}
