<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Repository\Review\CommentRepository;

class UserCommentsRequest extends AbstractValidatedRequest
{
    public function getSearchQuery(): string
    {
        return trim($this->request->query->getString('search'));
    }

    public function getOrderBy(): string
    {
        return $this->request->query->getString('order-by', CommentRepository::ORDER_CREATE_TIMESTAMP);
    }

    public function getState(): ?CommentStateEnum
    {
        return CommentStateEnum::tryFrom($this->request->query->getString('state', 'any'));
    }

    /**
     * @return int<1, max>
     */
    public function getPage(): int
    {
        return max(1, $this->request->query->getInt('page', 1));
    }

    protected function getValidationRules(): ValidationRules
    {
        $orderBys = [CommentRepository::ORDER_CREATE_TIMESTAMP, CommentRepository::ORDER_UPDATE_TIMESTAMP];
        $states   = array_merge(['any'], CommentStateEnum::values());

        return new ValidationRules(
            [
                'query' => [
                    'search'   => 'string',
                    'order-by' => 'string|in:' . implode(',', $orderBys),
                    'state'    => 'string|in:' . implode(',', $states),
                    'page'     => 'integer|min:1'
                ],
            ],
            true
        );
    }
}
