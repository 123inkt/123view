<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Review;

use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;
use DR\GitCommitNotification\Tests\DataFixtures\CodeReviewFixtures;
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
     */
    public function testFindOneByCommitHash(): void
    {
    }

    /**
     * @covers ::findByUrl
     */
    public function testFindByUrl(): void
    {
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
        return [UserFixtures::class, CodeReviewFixtures::class];
    }
}
