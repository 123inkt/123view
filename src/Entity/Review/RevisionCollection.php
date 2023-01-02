<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Service\Revision\RevisionSorter;

/**
 * @extends ArrayCollection<int, Revision>
 */
class RevisionCollection extends ArrayCollection
{
    /**
     * @param Revision[] $elements
     */
    public function __construct(array $elements = [])
    {
        usort($elements, [new RevisionSorter(), 'sortByCreateTimestamp']);
        parent::__construct($elements);
    }
}
