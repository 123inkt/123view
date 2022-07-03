<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Config;

use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Repository\Config\RecipientRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Config\RecipientRepository
 */
class RecipientRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $repository = new RecipientRepository($this->registry);
        static::assertSame(Recipient::class, $repository->getClassName());
    }

    protected function getRepositoryEntityClassString(): string
    {
        return Recipient::class;
    }
}
