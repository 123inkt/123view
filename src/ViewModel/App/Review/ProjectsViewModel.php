<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Config\Repository;

class ProjectsViewModel
{
    /**
     * @param Repository[] $repositories
     */
    public function __construct(private readonly array $repositories) {
    }

    public function getRepositories(): array
    {
        return $this->repositories;
    }
}
