<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Repository\Review\CommentOverviewRepository;

class CommentOverviewRequest extends AbstractValidatedRequest
{
    public function getSearchQuery(): string
    {
        return trim((string)$this->request->query->get('search', ''));
    }

    public function getOrderBy(): string
    {
        return $this->request->query->get('order-by', CommentOverviewRepository::ORDER_CREATE_TIMESTAMP);
    }

    public function getPage(): int
    {
        return max(1, $this->request->query->getInt('page', 1));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        $orderBys = [
            CommentOverviewRepository::ORDER_CREATE_TIMESTAMP,
            CommentOverviewRepository::ORDER_UPDATE_TIMESTAMP
        ];

        return new ValidationRules(
            [
                'query' => [
                    'search'   => 'string',
                    'order-by' => 'string|in:' . implode(',', $orderBys),
                    'page'     => 'integer|min:1'
                ],
            ],
            true
        );
    }
}
