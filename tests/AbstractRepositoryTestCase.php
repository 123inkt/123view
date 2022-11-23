<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Tests\Helper\QueryBuilderAssertion;
use PHPUnit\Framework\MockObject\MockObject;
use function PHPUnit\Framework\once;

abstract class AbstractRepositoryTestCase extends AbstractTestCase
{
    /** @var MockObject&EntityManager */
    protected MockObject $objectManager;
    /** @var ManagerRegistry&MockObject */
    protected ManagerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = $this->createMock(EntityManager::class);
        $this->objectManager->method('getClassMetadata')->willReturn(new ClassMetadata($this->getRepositoryEntityClassString()));
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry->method('getManagerForClass')->willReturn($this->objectManager);
    }

    /**
     * @template T of ServiceEntityRepository
     * @phpstan-param class-string<T> $classString
     * @phpstan-return T
     */
    final protected function getRepository(string $classString): ServiceEntityRepository
    {
        return new $classString($this->registry);
    }

    final protected function expectWrapInTransaction(): void
    {
        $this->objectManager->expects(self::once())->method('wrapInTransaction')->willReturnCallback(fn($callable) => $callable());
    }

    final protected function expectPersist(object $object): void
    {
        $this->objectManager->expects(self::once())->method('persist')->with($object);
    }

    final protected function expectRemove(object $object): void
    {
        $this->objectManager->expects(self::once())->method('remove')->with($object);
    }

    final protected function neverExpectFlush(): void
    {
        $this->objectManager->expects(self::never())->method('flush');
    }

    final protected function expectFlush(): void
    {
        $this->objectManager->expects(self::once())->method('flush');
    }

    final protected function expectCreateQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilderAssertion
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $builderAssertion = new QueryBuilderAssertion($this, $queryBuilder);
        $builderAssertion->select($alias);
        $builderAssertion->from($this->getRepositoryEntityClassString(), $alias, $indexBy);

        $queryBuilder->method('expr')->willReturn(new Expr());

        $this->objectManager->expects(once())->method('createQueryBuilder')->willReturn($queryBuilder);

        return $builderAssertion;
    }

    /**
     * @return class-string
     */
    abstract protected function getRepositoryEntityClassString(): string;
}
