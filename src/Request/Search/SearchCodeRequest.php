<?php
declare(strict_types=1);

namespace DR\Review\Request\Search;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Model\Search\SearchFilter;
use DR\Utils\Arrays;

class SearchCodeRequest extends AbstractValidatedRequest
{
    public function getFilter(): SearchFilter
    {
        $extensions = Arrays::explode(',', $this->request->query->getString('extension'));

        return new SearchFilter(
            trim($this->request->query->getString('search')),
            count($extensions) === 0 ? null : $extensions,
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
                    'search'    => 'required|string',
                    'extension' => 'string|regex:/^[a-zA-Z0-9]{1,5}(,[a-zA-Z0-9]{1,5})*$/',
                    'all'       => 'string',
                    'regex'     => 'string|in:true,false',
                ]
            ]
        );
    }
}
