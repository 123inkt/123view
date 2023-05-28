<?php
declare(strict_types=1);

namespace DR\Review\Repository\Report;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;

/**
 * @extends ServiceEntityRepository<CodeCoverageFile>
 * @method CodeCoverageFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeCoverageFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeCoverageFile[]    findAll()
 * @method CodeCoverageFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeCoverageFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeCoverageFile::class);
    }

    /**
     * @param Revision[] $revisions
     */
    public function findOneByRevisions(Repository $repository, array $revisions, string $filePath): ?CodeCoverageFile
    {
        $hashes = array_values(array_map(static fn(Revision $rev): string => (string)$rev->getCommitHash(), $revisions));
        $query  = $this->createQueryBuilder('f')
            ->leftJoin('f.report', 'r')
            ->where('r.repository = :repositoryId')
            ->andWhere('r.commitHash in (:hashes)')
            ->andWhere('f.file = :filePath')
            ->setParameter('repositoryId', $repository->getId())
            ->setParameter('hashes', $hashes)
            ->setParameter('filePath', $filePath)
            ->orderBy('r.createTimestamp', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult(Query::HYDRATE_OBJECT);
    }
}
