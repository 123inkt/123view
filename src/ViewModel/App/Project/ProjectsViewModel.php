<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use DR\Review\ViewModel\ViewModelInterface;
use Symfony\Component\Serializer\Attribute\Groups;

class ProjectsViewModel implements ViewModelInterface
{
    /**
     * @param Repository[]    $repositories
     * @param array<int, int> $revisionCount
     */
    public function __construct(
        #[Groups('app:projects')]
        public readonly array $repositories,
        #[Groups('app:projects')]
        public readonly array $revisionCount,
        #[Groups('app:projects')]
        public readonly TimelineViewModel $timeline,
        #[Groups('app:projects')]
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
