<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Config;

use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;
use DR\GitCommitNotification\Tests\DataFixtures\RepositoryFixtures;
use DR\GitCommitNotification\Utility\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Config\RepositoryRepository
 * @covers ::__construct
 */
class RepositoryRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::findByUpdateRevisions
     * @throws Exception
     */
    public function testFindByUpdateRevisionsWithinRange(): void
    {
        $repositoryRepository = self::getService(RepositoryRepository::class);
        $repository           = Assert::notNull($repositoryRepository->findOneBy(['name' => 'repository']));

        $repository->setUpdateRevisionsInterval(500);
        $repository->setUpdateRevisionsTimestamp(time() - 600);
        $repositoryRepository->save($repository, true);

        static::assertCount(1, $repositoryRepository->findByUpdateRevisions());
    }

    /**
     * @covers ::findByUpdateRevisions
     * @throws Exception
     */
    public function testFindByUpdateRevisionsOutsideRange(): void
    {
        $repositoryRepository = self::getService(RepositoryRepository::class);
        $repository           = Assert::notNull($repositoryRepository->findOneBy(['name' => 'repository']));

        $repository->setUpdateRevisionsInterval(500);
        $repository->setUpdateRevisionsTimestamp(time() - 100);
        $repositoryRepository->save($repository, true);

        static::assertCount(0, $repositoryRepository->findByUpdateRevisions());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RepositoryFixtures::class];
    }
}
