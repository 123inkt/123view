<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Review;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;
use DR\GitCommitNotification\Tests\DataFixtures\RevisionFixtures;
use DR\GitCommitNotification\Utility\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Review\RevisionRepository
 * @covers ::__construct
 */
class RevisionRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::flush
     */
    public function testFlush(): void
    {
    }

    /**
     * @covers ::exists
     * @throws Exception
     */
    public function testExists(): void
    {
        $revisionRepository = self::getService(RevisionRepository::class);

        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('foobar');
        static::assertFalse($revisionRepository->exists($repository, $revision));

        $revision = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        static::assertTrue($revisionRepository->exists(Assert::notNull($revision->getRepository()), $revision));
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

        static::assertCount(1, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, '', null));
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
        static::assertCount(1, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, '', false));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RevisionFixtures::class];
    }
}
