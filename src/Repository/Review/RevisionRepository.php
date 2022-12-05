<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Revision;
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
     * @throws Throwable
     */
    public function saveAll(Repository $repository, array $revisions): void
    {
        $em = $this->getEntityManager();

        $em->wrapInTransaction(function () use ($repository, $revisions): void {
            foreach ($revisions as $revision) {
                $entityExists = $this->findOneBy(['repository' => (int)$repository->getId(), 'commitHash' => $revision->getCommitHash()]) !== null;
                if ($entityExists) {
                    continue;
                }
                parent::save($revision);
            }
        });

        try {
            $em->flush();
            // @codeCoverageIgnoreStart
        } catch (Throwable $exception) {
            $this->registry->resetManager();
            throw $exception;
            // @codeCoverageIgnoreEnd
        }
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
}
