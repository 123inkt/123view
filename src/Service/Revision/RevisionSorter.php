<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Revision\Revision;

class RevisionSorter
{
    private const string SORT_BY_CREATE_TIMESTAMP = 'timestamp';
    private const string SORT_BY_UUID             = 'sort';

    /**
     * @param Revision[] $revisions
     *
     * @return Revision[]
     */
    public function sort(array $revisions): array
    {
        $sortBy = self::getSortBy($revisions);
        usort(
            $revisions,
            static function (Revision $left, Revision $right) use ($sortBy): int {
                if ($sortBy === self::SORT_BY_CREATE_TIMESTAMP) {
                    return $left->getCreateTimestamp() <=> $right->getCreateTimestamp();
                }

                return strcmp($left->getSort(), $right->getSort());
            }
        );

        return $revisions;
    }

    /**
     * If for all revisions the UUID is set, sort by UUID
     *
     * @param Revision[] $revisions
     */
    private static function getSortBy(array $revisions): string
    {
        foreach ($revisions as $revision) {
            if ($revision->getSort() === null) {
                return self::SORT_BY_CREATE_TIMESTAMP;
            }
        }

        return self::SORT_BY_UUID;
    }
}
