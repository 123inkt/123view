<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Repository\Config;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Entity\Config\Rule;

/**
 * @extends ServiceEntityRepository<Rule>
 * @method Rule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rule[]    findAll()
 * @method Rule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rule::class);
    }

    public function add(Rule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Rule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @phpstan-param Frequency::* $frequency
     * @return Rule[] Returns an array of Rule objects
     * @codeCoverageIgnore  getQuery returns final class. Didn't find solution to mock it.
     */
    public function getActiveRulesForFrequency(bool $active, string $frequency): array
    {
        /** @var Rule[] $result */
        $result = $this->createQueryBuilder('r')
            ->leftJoin('r.ruleOptions', 'o')
            ->andWhere('r.active = :active')
            ->andWhere('o.frequency = :frequency')
            ->setParameter('active', $active ? 1 : 0)
            ->setParameter('frequency', $frequency)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
