<?php
declare(strict_types=1);

namespace DR\Review\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class FileReviewRequest extends AbstractValidatedRequest
{
    public function getFilePath(): string
    {
        return $this->request->query->get('filePath', '');
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['query' => ['filePath' => 'required|string|filled']]);
    }
}
