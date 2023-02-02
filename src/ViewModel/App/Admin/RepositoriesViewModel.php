<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Repository\Repository;

class RepositoriesViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param Repository[] $repositories
     */
    public function __construct(public readonly array $repositories)
    {
    }
}
