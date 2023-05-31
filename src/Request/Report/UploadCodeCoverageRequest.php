<?php
declare(strict_types=1);

namespace DR\Review\Request\Report;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Service\Report\Coverage\Parser\CloverParser;

class UploadCodeCoverageRequest extends AbstractValidatedRequest
{
    public function getBranchId(): ?string
    {
        return $this->request->query->get('branchId');
    }

    public function getBasePath(): string
    {
        return $this->request->query->get('basePath', '');
    }

    public function getFormat(): string
    {
        return $this->request->query->get('format', CloverParser::FORMAT);
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
                    'basePath' => 'string',
                    'branchId' => 'string|min:1|max:255',
                    'format'   => 'string|in:' . CloverParser::FORMAT
                ]
            ]
        );
    }
}
