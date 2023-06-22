<?php
declare(strict_types=1);

namespace DR\Review\Request\User;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class UserMentionSuggestRequest extends AbstractValidatedRequest
{
    public function getSearch(): string
    {
        return (string)$this->request->query->get('search');
    }

    /**
     * @return int[]
     */
    public function getPreferredUserIds(): array
    {
        $preferredUserIds = $this->request->query->get('preferredUserIds', '');
        if ($preferredUserIds === '') {
            return [];
        }

        return array_map('intval', explode(',', $preferredUserIds));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'search'           => 'string',
                    'preferredUserIds' => 'string|regex:/^(\d+(,\d+)*)?$/'
                ]
            ]
        );
    }
}
