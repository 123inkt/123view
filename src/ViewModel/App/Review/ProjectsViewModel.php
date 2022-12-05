<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Entity\Repository\Repository;

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
