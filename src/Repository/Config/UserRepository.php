<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Config;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Config\User;

/**
 * @extends ServiceEntityRepository<User>
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return User[]
     */
    public function findBySearchQuery(string $searchQuery, int $limit): array
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.name LIKE :search or u.email LIKE :search')
            ->setParameter('search', addcslashes($searchQuery, '%_') . '%')
            ->orderBy('u.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery();

        /** @var User[] $result */
        $result = $query->getResult();

        return $result;
    }
}
