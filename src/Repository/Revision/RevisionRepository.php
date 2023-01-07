<?php
declare(strict_types=1);

namespace DR\Review\Repository\Revision;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use Throwable;

/**
 * @extends ServiceEntityRepository<Revision>
 * @method Revision|null find($id, $lockMode = null, $lockVersion = null)
 * @method Revision|null findOneBy(array $criteria, array $orderBy = null)
 * @method Revision[]    findAll()
 * @method Revision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevisionRepository extends ServiceEntityRepository
{
    public function __construct(private readonly ManagerRegistry $registry)
    {
        parent::__construct($registry, Revision::class);
    }

    /**
     * @param Revision[] $revisions
     *
     * @return Revision[] all saved and not already existing revisions
     * @throws Throwable
     */
    public function saveAll(Repository $repository, array $revisions): array
    {
        $em = $this->getEntityManager();

        /** @var Revision[] $revisions */
        $revisions = $em->wrapInTransaction(function () use ($repository, $revisions): array {
            foreach ($revisions as $index => $revision) {
                $entityExists = $this->findOneBy(['repository' => (int)$repository->getId(), 'commitHash' => $revision->getCommitHash()]) !== null;
                if ($entityExists) {
                    unset($revisions[$index]);
                    continue;
                }
                parent::save($revision);
            }

            return $revisions;
        });

        try {
            $em->flush();
            foreach ($revisions as $revision) {
                $em->detach($revision);
            }
            // @codeCoverageIgnoreStart
        } catch (Throwable $exception) {
            $this->registry->resetManager();
            throw $exception;
            // @codeCoverageIgnoreEnd
        }

        return $revisions;
    }

    /**
     * @return Paginator<Revision>
     */
    public function getPaginatorForSearchQuery(int $repositoryId, int $page, string $searchQuery, ?bool $attached): Paginator
    {
        $query = $this->createQueryBuilder('r')
            ->select('r', 'c')
            ->leftJoin('r.review', 'c')
            ->where('r.repository = :repositoryId')
            ->setParameter('repositoryId', $repositoryId)
            ->orderBy('r.createTimestamp', 'DESC')
            ->setFirstResult(max(0, $page - 1) * 50)
            ->setMaxResults(50);

        if ($searchQuery !== '') {
            $query->andWhere('r.title LIKE :searchQuery OR r.authorEmail LIKE :searchQuery OR r.authorName LIKE :searchQuery');
            $query->setParameter('searchQuery', '%' . addcslashes($searchQuery, '%_') . '%');
        }

        if ($attached !== null) {
            $query->andWhere($attached ? 'r.review IS NOT NULL' : 'r.review IS NULL');
        }

        return new Paginator($query->getQuery(), true);
    }

    /**
     * @return string[]
     */
    public function getCommitHashes(Repository $repository): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('r.commitHash');
        $qb->where('r.repository = :repositoryId');
        $qb->setParameter('repositoryId', $repository->getId());
        $result = $qb->getQuery()->getScalarResult();

        return array_column($result, 'commitHash');
    }
}
