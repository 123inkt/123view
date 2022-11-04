<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ArrayCollection<int, FileSeenStatus>
 */
class FileSeenStatusCollection extends ArrayCollection
{
    public function isSeen(string $filePath): bool
    {
        /** @var FileSeenStatus $status */
        foreach ($this as $status) {
            if (strcasecmp((string)$status->getFilePath(), $filePath) === 0) {
                return true;
            }
        }

        return false;
    }
}
