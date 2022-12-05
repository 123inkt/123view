<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Review\Revision;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\RepositoryFixtures;
use DR\Review\Tests\DataFixtures\RevisionFixtures;
use DR\Review\Utility\Assert;
use Exception;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\Repository\Review\RevisionRepository
 * @covers ::__construct
 */
class RevisionRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::saveAll
     * @throws Exception
     * @throws Throwable
     */
    public function testSaveAll(): void
    {
        $repository         = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $revisionRepository = self::getService(RevisionRepository::class);

        $revision = new Revision();
        $revision->setCommitHash('hash');
        $revision->setTitle('title');
        $revision->setDescription('description');
        $revision->setAuthorEmail('sherlock@example.com');
        $revision->setAuthorName('Sherlock Holmes');
        $revision->setCreateTimestamp(time());
        $revision->setRepository($repository);

        $revisionRepository->saveAll($repository, [$revision]);
        $revisionRepository->saveAll($repository, [$revision]);

        static::assertNotNull($revision->getId());
    }

    /**
     * @covers ::getPaginatorForSearchQuery
     * @throws Exception
     */
    public function testGetPaginatorForSearchQuery(): void
    {
        $revisionRepository = self::getService(RevisionRepository::class);
        $revision           = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $repositoryId       = (int)$revision->getRepository()?->getId();

        static::assertCount(2, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, '', null));
    }

    /**
     * @covers ::getPaginatorForSearchQuery
     * @throws Exception
     */
    public function testGetPaginatorForSearchQueryWithSearchQueryString(): void
    {
        $revisionRepository = self::getService(RevisionRepository::class);
        $revision           = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $repositoryId       = (int)$revision->getRepository()?->getId();

        static::assertCount(1, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, 'sherlock@example.com', null));
        static::assertCount(0, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, 'foobar', null));
    }

    /**
     * @covers ::getPaginatorForSearchQuery
     * @throws Exception
     */
    public function testGetPaginatorForSearchQueryWithCodeReviewAttached(): void
    {
        $revisionRepository = self::getService(RevisionRepository::class);
        $revision           = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $repositoryId       = (int)$revision->getRepository()?->getId();

        static::assertCount(0, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, '', true));
        static::assertCount(2, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, '', false));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RevisionFixtures::class, RepositoryFixtures::class];
    }
}
