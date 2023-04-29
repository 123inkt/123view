<?php
declare(strict_types=1);

namespace DR\Review\Request\Report;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class UploadCodeInspectionRequest extends AbstractValidatedRequest
{
    public function getIdentifier(): string
    {
        return $this->request->query->get('identifier', '');
    }

    public function getBasePath(): string
    {
        return $this->request->query->get('basePath', '');
    }

    public function getFormat(): string
    {
        return $this->request->query->get('format', 'checkstyle');
    }

    public function getData(): string
    {
        return $this->request->getContent();
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'identifier' => 'required|string|min:1|max:50',
                    'basePath'   => 'string',
                    'format'     => 'string|in:checkstyle,github,gitlab'
                ]
            ]
        );
    }
}
