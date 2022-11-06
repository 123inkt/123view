<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Review;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\User\User;

/**
 * @extends ServiceEntityRepository<CodeReview>
 * @method CodeReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeReview[]    findAll()
 * @method CodeReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeReview::class);
    }

    public function save(CodeReview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CodeReview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getCreateProjectId(int $repositoryId): int
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.repository = :repositoryId')
            ->setParameter('repositoryId', $repositoryId)
            ->orderBy('c.projectId', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        /** @var CodeReview|null $review */
        $review = $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return (int)$review?->getProjectId() + 1;
    }

    /**
     * @return Paginator<CodeReview>
     */
    public function getPaginatorForSearchQuery(User $user, int $repositoryId, int $page, string $searchQuery): Paginator
    {
        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.revisions', 'rv')
            ->where('r.repository = :repositoryId')
            ->setParameter('repositoryId', $repositoryId)
            ->orderBy('r.id', 'DESC')
            ->setFirstResult(max(0, $page - 1) * 50)
            ->setMaxResults(50);

        // TODO refactor to search query factory
        if ($searchQuery !== '') {
            if (preg_match('/id:(\d+)/', $searchQuery, $matches) === 1) {
                $query->andWhere('r.projectId = :id')->setParameter('id', $matches[1]);
                $searchQuery = trim(str_replace($matches[0], '', $searchQuery));
            }

            if (preg_match('/state:(\w+)/', $searchQuery, $matches) === 1) {
                $query->andWhere('r.state = :state')->setParameter('state', $matches[1]);
                $searchQuery = trim(str_replace($matches[0], '', $searchQuery));
            }

            if (preg_match('/author:(\w+)/', $searchQuery, $matches) === 1) {
                // search for current user
                if ($matches[1] === 'me') {
                    $query->andWhere('rv.authorEmail = :authorEmail');
                    $query->setParameter('authorEmail', (string)$user->getEmail());
                } else {
                    $query->andWhere('rv.authorEmail LIKE :searchAuthor OR rv.authorName LIKE :searchAuthor');
                    $query->setParameter('searchAuthor', '%' . addcslashes($matches[1], '%_') . '%');
                }
                $searchQuery = trim(str_replace($matches[0], '', $searchQuery));
            }

            if ($searchQuery !== '') {
                if (preg_match('/^\d+$/', $searchQuery) === 1) {
                    $query->andWhere('r.title LIKE :title OR r.projectId = :projectId')
                        ->setParameter('projectId', $searchQuery)
                        ->setParameter('title', '%' . addcslashes($searchQuery, "%_") . '%');
                } else {
                    $query->andWhere('r.title LIKE :title')
                        ->setParameter('title', '%' . addcslashes($searchQuery, "%_") . '%');
                }
            }
        }

        return new Paginator($query->getQuery(), false);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByReferenceId(int $repositoryId, string $referenceId): ?CodeReview
    {
        /** @var CodeReview|null $review */
        $review = $this->createQueryBuilder('c')
            ->where('c.referenceId = :referenceId')
            ->andWhere('c.repository = :repositoryId')
            ->orderBy('c.id', 'DESC')
            ->setParameter('referenceId', $referenceId)
            ->setParameter('repositoryId', $repositoryId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return $review;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByCommitHash(int $repositoryId, string $commitHash): ?CodeReview
    {
        /** @var CodeReview|null $review */
        $review = $this->createQueryBuilder('c')
            ->innerJoin('c.revisions', 'r', 'WITH', 'r.commitHash = :commitHash')
            ->where('c.repository = :repositoryId')
            ->setParameter('commitHash', $commitHash)
            ->setParameter('repositoryId', $repositoryId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        return $review;
    }
}
