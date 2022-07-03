<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Config;

use DR\GitCommitNotification\Repository\Config\FilterRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Config\FilterRepository
 */
class FilterRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $repository = new FilterRepository($this->registry);
        static::assertSame('class-meta-data', $repository->getClassName());
    }
}
