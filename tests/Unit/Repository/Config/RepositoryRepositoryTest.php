<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Config;

use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\RepositoryFixtures;
use DR\Utils\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\Review\Repository\Config\RepositoryRepository
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
     * @covers ::findByValidateRevisions
     * @throws Exception
     */
    public function testFindByValidateRevisionsWithinRange(): void
    {
        $repositoryRepository = self::getService(RepositoryRepository::class);
        $repository           = Assert::notNull($repositoryRepository->findOneBy(['name' => 'repository']));

        $repository->setValidateRevisionsInterval(500);
        $repository->setValidateRevisionsTimestamp(time() - 600);
        $repositoryRepository->save($repository, true);

        static::assertCount(1, $repositoryRepository->findByValidateRevisions());
    }

    /**
     * @covers ::findByValidateRevisions
     * @throws Exception
     */
    public function testFindByValidateRevisionsOutsideRange(): void
    {
        $repositoryRepository = self::getService(RepositoryRepository::class);
        $repository           = Assert::notNull($repositoryRepository->findOneBy(['name' => 'repository']));

        $repository->setValidateRevisionsInterval(500);
        $repository->setValidateRevisionsTimestamp(time() - 100);
        $repositoryRepository->save($repository, true);

        static::assertCount(0, $repositoryRepository->findByValidateRevisions());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RepositoryFixtures::class];
    }
}
