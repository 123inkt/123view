<?php
declare(strict_types=1);

namespace DR\Review\Request\Search;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class SearchBranchRequest extends AbstractValidatedRequest
{
    /**
     * @return non-empty-string
     */
    public function getSearchQuery(): string
    {
        return trim($this->request->query->getString('search'));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['query' => ['search' => 'required|string|min:1']]);
    }
}
