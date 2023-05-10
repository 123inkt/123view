<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;

class ProjectBranchesViewModel
{
    /**
     * @param string[] $branches
     */
    public function __construct(public readonly Repository $repository, public readonly array $branches)
    {
    }
}
