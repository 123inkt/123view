<?php
declare(strict_types=1);

namespace DR\Review\Request\Reviews;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Repository\Review\CodeReviewQueryBuilder;

class SearchReviewsRequest extends AbstractValidatedRequest
{
    public function getSearchQuery(): string
    {
        return trim($this->request->query->get('search', 'state:open'));
    }

    public function getOrderBy(): string
    {
        return $this->request->query->get('orderBy', CodeReviewQueryBuilder::ORDER_UPDATE_TIMESTAMP);
    }

    public function getPage(): int
    {
        return $this->request->query->getInt('page', 1);
    }

    protected function getValidationRules(): ?ValidationRules
    {
        $orderBys = [CodeReviewQueryBuilder::ORDER_CREATE_TIMESTAMP, CodeReviewQueryBuilder::ORDER_UPDATE_TIMESTAMP];

        return new ValidationRules(
            [
                'query' => [
                    'search'  => 'string',
                    'orderBy' => 'string|in:' . implode(',', $orderBys),
                    'page'    => 'integer|min:1'
                ],
            ],
            true
        );
    }
}
