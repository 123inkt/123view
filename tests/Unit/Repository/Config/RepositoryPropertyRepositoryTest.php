<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Config;

use DR\GitCommitNotification\Entity\Config\RepositoryProperty;
use DR\GitCommitNotification\Repository\Config\RepositoryPropertyRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Config\RepositoryPropertyRepository
 */
class RepositoryPropertyRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $repository = new RepositoryPropertyRepository($this->registry);
        static::assertSame(RepositoryProperty::class, $repository->getClassName());
    }

    protected function getRepositoryEntityClassString(): string
    {
        return RepositoryProperty::class;
    }
}
