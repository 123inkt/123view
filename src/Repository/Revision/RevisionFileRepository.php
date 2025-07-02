<?php

declare(strict_types=1);

namespace DR\Review\Repository\Revision;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionFile;
use DR\Review\Model\Review\RevisionFileChange;

/**
 * @extends ServiceEntityRepository<RevisionFile>
 */
class RevisionFileRepository extends ServiceEntityRepository
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RevisionFile::class);
    }

    /**
     * @param Revision[] $revisions
     *
     * @return array<int, RevisionFileChange> [revisionId => RevisionFileChange]
     */
    public function getFileChanges(array $revisions): array
    {
        $qb = $this->createQueryBuilder('f')
            ->select('r.id, COUNT(1) AS file_count, SUM(f.linesAdded) AS lines_added, SUM(f.linesRemoved) AS lines_removed')
            ->innerJoin('f.revision', 'r')
            ->where('r IN (:revisions)')
            ->groupBy('r.id')
            ->setParameter('revisions', $revisions);

        $result = [];
        foreach ($qb->getQuery()->getArrayResult() as $entry) {
            $revisionId          = (int)$entry['id'];
            $result[$revisionId] = new RevisionFileChange(
                $revisionId,
                (int)$entry['file_count'],
                (int)$entry['lines_added'],
                (int)$entry['lines_removed']
            );
        }

        return $result;
    }
}
