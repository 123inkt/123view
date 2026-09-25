<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Search;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;

readonly class SearchBranchViewModel
{
    /**
     * @codeCoverageIgnore Simple DTO
     *
     * @param array<int, string[]>                  $branches
     * @param Repository[]                          $repositories
     * @param array<int, array<string, CodeReview>> $reviews
     */
    public function __construct(public array $branches, public array $repositories, public array $reviews, public string $searchQuery)
    {
    }
}
