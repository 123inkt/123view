<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;

abstract class AbstractRepositoryTestCase extends AbstractTestCase
{
    /** @var MockObject&EntityManagerInterface */
    protected MockObject $objectManager;
    /** @var ManagerRegistry&MockObject */
    protected ManagerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = $this->createMock(EntityManagerInterface::class);
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

    /**
     * @return class-string
     */
    abstract protected function getRepositoryEntityClassString(): string;
}
