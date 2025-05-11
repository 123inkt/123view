<?php
declare(strict_types=1);

namespace DR\Review\Request\Search;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Utils\Arrays;

class SearchCodeRequest extends AbstractValidatedRequest
{
    public function getSearchQuery(): string
    {
        return trim($this->request->query->getString('search'));
    }

    public function getSearchMode(): string
    {
        $mode = $this->request->query->getString('mode');

        return $mode === '' ? 'string' : $mode;
    }

    /**
     * @return non-empty-array<string>|null
     */
    public function getExtensions(): ?array
    {
        $extensions = Arrays::explode(',', $this->request->query->getString('extension'));

        return count($extensions) === 0 ? null : $extensions;
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'search'    => 'required|string',
                    'extension' => 'string|regex:/^[a-zA-Z0-9]{1,5}(,[a-zA-Z0-9]{1,5})*$/',
                    'mode'      => 'string|in:string,regex',
                ]
            ]
        );
    }
}
