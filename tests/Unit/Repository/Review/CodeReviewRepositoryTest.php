<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Review;

use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;
use DR\GitCommitNotification\Tests\DataFixtures\CodeReviewFixtures;
use DR\GitCommitNotification\Tests\DataFixtures\RevisionFixtures;
use DR\GitCommitNotification\Tests\DataFixtures\UserFixtures;
use DR\GitCommitNotification\Utility\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Review\CodeReviewRepository
 * @covers ::__construct
 */
class CodeReviewRepositoryTest extends AbstractRepositoryTestCase
{
    private CodeReviewRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = static::getService(CodeReviewRepository::class);
    }

    /**
     * @covers ::getCreateProjectId
     * @throws Exception
     */
    public function testGetCreateProjectId(): void
    {
        $repository = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $review     = Assert::notNull($this->repository->findOneBy(['title' => 'title']));

        $projectId = $this->repository->getCreateProjectId((int)$repository->getId());
        static::assertSame((int)$review->getProjectId() + 1, $projectId);
    }

    /**
     * @covers ::findOneByReferenceId
     * @throws Exception
     */
    public function testFindOneByReferenceId(): void
    {
        $repository = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));

        static::assertNotNull($this->repository->findOneByReferenceId((int)$repository->getId(), 'reference'));
    }

    /**
     * @covers ::findOneByCommitHash
     * @throws Exception
     */
    public function testFindOneByCommitHash(): void
    {
        $revisionRepository = static::getService(RevisionRepository::class);

        $repository = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $revision   = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $review     = Assert::notNull($this->repository->findOneBy(['title' => 'title']));

        $revision->setReview($review);
        $revisionRepository->save($revision, true);

        static::assertNotNull($this->repository->findOneByCommitHash((int)$repository->getId(), RevisionFixtures::COMMIT_HASH));
    }

    /**
     * @covers ::findByUrl
     * @throws Exception
     */
    public function testFindByUrl(): void
    {
        $repository = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));

        static::assertNotNull($this->repository->findByUrl((string)$repository->getName(), CodeReviewFixtures::PROJECT_ID));
        static::assertNull($this->repository->findByUrl('foobar', CodeReviewFixtures::PROJECT_ID));
        static::assertNull($this->repository->findByUrl((string)$repository->getName(), -1));
    }

    /**
     * @covers ::getPaginatorForSearchQuery
     */
    public function testGetPaginatorForSearchQuery(): void
    {
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class, RevisionFixtures::class];
    }
}
