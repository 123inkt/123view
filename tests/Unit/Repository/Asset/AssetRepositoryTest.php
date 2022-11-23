<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Asset;

use DR\GitCommitNotification\Entity\Asset\Asset;
use DR\GitCommitNotification\Repository\Asset\AssetRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Asset\AssetRepository
 * @covers ::__construct
 */
class AssetRepositoryTest extends AbstractRepositoryTestCase
{
    private AssetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(AssetRepository::class);
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $asset = new Asset();

        $this->expectPersist($asset);
        $this->expectFlush();
        $this->repository->save($asset, true);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $asset = new Asset();

        $this->expectRemove($asset);
        $this->expectFlush();
        $this->repository->remove($asset, true);
    }

    protected function getRepositoryEntityClassString(): string
    {
        return AssetRepository::class;
    }
}
