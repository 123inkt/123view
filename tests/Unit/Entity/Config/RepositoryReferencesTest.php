<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\RepositoryReference;
use DR\GitCommitNotification\Entity\Config\RepositoryReferences;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\RepositoryReferences
 */
class RepositoryReferencesTest extends AbstractTest
{
    /**
     * @covers ::getRepositories
     * @covers ::addRepository
     */
    public function testGetRepositories(): void
    {
        $references = new RepositoryReferences();
        static::assertEmpty($references->getRepositories());

        $reference = new RepositoryReference();
        $references->addRepository($reference);
        static::assertSame([$reference], $references->getRepositories());
    }
}
