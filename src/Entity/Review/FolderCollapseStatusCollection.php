<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ArrayCollection<int, FolderCollapseStatus>
 */
class FolderCollapseStatusCollection extends ArrayCollection
{
    public function isCollapsed(string $path): bool
    {
        /** @var FolderCollapseStatus $status */
        foreach ($this as $status) {
            if (strcasecmp($status->getPath(), $path) === 0) {
                return true;
            }
        }

        return false;
    }
}
