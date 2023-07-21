<?php
declare(strict_types=1);

namespace DR\Review\Request\Project;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class ProjectBranchRequest extends AbstractValidatedRequest
{
    public function getSearchQuery(): ?string
    {
        $value = trim($this->request->query->get('search', ''));

        return trim($value) === '' ? null : $value;
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'search' => 'string'
                ]
            ]
        );
    }
}
