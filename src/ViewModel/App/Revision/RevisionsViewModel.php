<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Revision;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;

readonly class RevisionsViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param Revision[]                   $revisions
     * @param array<int, CodeReview>       $reviews [id, CodeReview]
     * @param PaginatorViewModel<Revision> $paginator
     */
    public function __construct(
        public Repository $repository,
        public array $revisions,
        public array $reviews,
        public PaginatorViewModel $paginator,
        public string $searchQuery
    ) {
    }
}
