<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;

class ProjectsViewModel
{
    /**
     * @param Repository[]    $repositories
     * @param array<int, int> $revisionCount
     */
    public function __construct(
        public readonly array $repositories,
        public readonly array $revisionCount,
        public readonly TimelineViewModel $timeline,
        public readonly string $searchQuery
    ) {
    }

    /**
     * @return Repository[]
     */
    public function getFavoriteRepositories(): array
    {
        $repositories = [];
        foreach ($this->repositories as $repository) {
            if ($repository->isFavorite()) {
                $repositories[] = $repository;
            }
        }

        return $repositories;
    }

    /**
     * @return Repository[]
     */
    public function getRegularRepositories(): array
    {
        $repositories = [];
        foreach ($this->repositories as $repository) {
            if ($repository->isFavorite() === false) {
                $repositories[] = $repository;
            }
        }

        return $repositories;
    }
}
