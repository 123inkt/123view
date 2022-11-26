<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Config\Repository;

/**
 * @codeCoverageIgnore
 */
class ProjectsViewModel
{
    /**
     * @param Repository[] $favoriteRepositories
     * @param Repository[] $repositories
     */
    public function __construct(public readonly array $favoriteRepositories, public readonly array $repositories)
    {
    }
}
