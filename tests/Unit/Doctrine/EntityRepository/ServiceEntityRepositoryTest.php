<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Doctrine\EntityRepository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Doctrine\EntityRepository\ServiceEntityRepository
 * @covers ::__construct
 */
class ServiceEntityRepositoryTest extends AbstractTestCase
{
    private ObjectManager&MockObject $objectManager;
    private ServiceEntityRepository  $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->objectManager = $this->createMock(EntityManager::class);
        $meta                = $this->createMock(ClassMetadata::class);
        $registry            = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->objectManager);
        $this->objectManager->method('getClassMetadata')->willReturn($meta);

        $this->repository = new ServiceEntityRepository($registry, stdClass::class);
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $entity = new stdClass();

        $this->objectManager->expects(self::exactly(2))->method('persist')->with($entity);
        $this->objectManager->expects(self::once())->method('flush')->with();

        $this->repository->save($entity, true);
        $this->repository->save($entity);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $entity = new stdClass();

        $this->objectManager->expects(self::exactly(2))->method('remove')->with($entity);
        $this->objectManager->expects(self::once())->method('flush')->with();

        $this->repository->remove($entity, true);
        $this->repository->remove($entity);
    }
}
