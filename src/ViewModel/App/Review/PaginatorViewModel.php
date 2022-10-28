<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatorViewModel
{
    /**
     * @param Paginator<object> $paginator
     */
    public function __construct(private readonly Paginator $paginator, private readonly int $page)
    {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @codeCoverageIgnore hard to mock as Query is final...
     */
    public function getLastPage(): int
    {
        return (int)ceil($this->paginator->count() / (int)$this->paginator->getQuery()->getMaxResults());
    }
}
