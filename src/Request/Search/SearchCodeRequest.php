<?php
declare(strict_types=1);

namespace DR\Review\Request\Search;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class SearchCodeRequest extends AbstractValidatedRequest
{
    public function getSearchQuery(): string
    {
        return trim($this->request->query->getString('search'));
    }

    public function getExtension(): ?string
    {
        $extension = trim($this->request->query->getString('extension'));

        return $extension === '' ? null : $extension;
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'search'    => 'required|string',
                    'extension' => 'string|regex:/^[a-zA-Z0-9.]{0,5}$/',
                ]
            ]
        );
    }
}
