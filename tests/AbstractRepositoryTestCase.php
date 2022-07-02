<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @template T of ServiceEntityRepository
 */
abstract class AbstractRepositoryTestCase extends AbstractTestCase
{
    /** @var MockObject&EntityManagerInterface */
    private MockObject $objectManager;
    /** @var T */
    protected ServiceEntityRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = $this->createMock(EntityManagerInterface::class);
        $this->objectManager->method('getClassMetadata')->willReturn($this->createMock(ClassMetadata::class));
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->objectManager);
        $this->repository = new ($this->getRepositoryClass())($registry);
    }

    protected function expectPersist(object $object): void
    {
        $this->objectManager->expects(self::once())->method('persist')->with($object);
    }

    protected function expectRemove(object $object): void
    {
        $this->objectManager->expects(self::once())->method('remove')->with($object);
    }

    protected function neverExpectFlush(): void
    {
        $this->objectManager->expects(self::never())->method('flush');
    }

    protected function expectFlush(): void
    {
        $this->objectManager->expects(self::once())->method('flush');
    }

    /**
     * @return class-string<T>
     */
    abstract public function getRepositoryClass(): string;
}
