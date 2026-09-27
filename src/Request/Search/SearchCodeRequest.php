<?php
declare(strict_types=1);

namespace DR\Review\Request\Search;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Model\Search\SearchFilter;

class SearchCodeRequest extends AbstractValidatedRequest
{
    public function getFilter(): SearchFilter
    {
        $filename = trim($this->request->query->getString('filename'));

        return new SearchFilter(
            trim($this->request->query->getString('search')),
            $filename === '' ? null : $filename,
            $this->request->query->getBoolean('regex'),
        );
    }

    public function isShowAll(): bool
    {
        return $this->request->query->getBoolean('all');
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'search'   => 'required|string',
                    'filename' => 'string',
                    'all'      => 'string',
                    'regex'    => 'string|in:true,false',
                ]
            ]
        );
    }
}
