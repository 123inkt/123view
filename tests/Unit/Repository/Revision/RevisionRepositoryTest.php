<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Revision;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\RepositoryFixtures;
use DR\Review\Tests\DataFixtures\RevisionFixtures;
use DR\Utils\Assert;
use Exception;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\Repository\Revision\RevisionRepository
 * @covers ::__construct
 */
class RevisionRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::getRepositoryRevisionCount
     * @throws Throwable
     */
    public function testGetRepositoryRevisionCount(): void
    {
        $repository    = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $revisionCount = self::getService(RevisionRepository::class)->getRepositoryRevisionCount();

        static::assertSame([(int)$repository->getId() => 2], $revisionCount);
    }

    /**
     * @covers ::saveAll
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

        static::assertCount(1, $revisionRepository->saveAll($repository, [$revision]));
        static::assertCount(0, $revisionRepository->saveAll($repository, [$revision]));

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
        $repositoryId       = (int)$revision->getRepository()->getId();

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
        $repositoryId       = (int)$revision->getRepository()->getId();

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
        $repositoryId       = (int)$revision->getRepository()->getId();

        static::assertCount(0, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, '', true));
        static::assertCount(2, $revisionRepository->getPaginatorForSearchQuery($repositoryId, 1, '', false));
    }

    /**
     * @covers ::getCommitHashes
     * @throws Exception
     */
    public function testGetCommitHashes(): void
    {
        $revisionRepository = self::getService(RevisionRepository::class);
        $revision           = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $repository         = $revision->getRepository();

        static::assertNotNull($repository);

        $hashes = $revisionRepository->getCommitHashes($repository);
        static::assertSame([RevisionFixtures::COMMIT_HASH_A, RevisionFixtures::COMMIT_HASH_B], $hashes);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RevisionFixtures::class, RepositoryFixtures::class];
    }
}
