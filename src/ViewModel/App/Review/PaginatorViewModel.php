<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @template T
 * @codeCoverageIgnore
 */
class PaginatorViewModel
{
    /**
     * @param Paginator<T> $paginator
     */
    public function __construct(
        private readonly Paginator $paginator,
        #[Groups('app:paginator')]
        public readonly int $page
    ) {
    }

    /**
     * @codeCoverageIgnore hard to mock as Query is final...
     */
    #[Groups('app:paginator')]
    public function getLastPage(): int
    {
        return (int)ceil($this->paginator->count() / (int)$this->paginator->getQuery()->getMaxResults());
    }
}
