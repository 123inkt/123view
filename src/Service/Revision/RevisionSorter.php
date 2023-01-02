<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Review\Revision;

class RevisionSorter
{
    public function sortByCreateTimestamp(Revision $left, Revision $right): int
    {
        return (int)$left->getCreateTimestamp() <=> (int)$right->getCreateTimestamp();
    }
}
