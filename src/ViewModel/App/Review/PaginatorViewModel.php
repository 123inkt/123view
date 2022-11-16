<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @template T
 * @codeCoverageIgnore
 */
class PaginatorViewModel
{
    /**
     * @param Paginator<T> $paginator
     */
    public function __construct(private readonly Paginator $paginator, public readonly int $page)
    {
    }

    /**
     * @codeCoverageIgnore hard to mock as Query is final...
     */
    public function getLastPage(): int
    {
        return (int)ceil($this->paginator->count() / (int)$this->paginator->getQuery()->getMaxResults());
    }
}
