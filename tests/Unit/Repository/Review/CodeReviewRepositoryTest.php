<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Tests\DataFixtures\RevisionFixtures;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Review\Utility\Arrays;
use DR\Review\Utility\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\Review\Repository\Review\CodeReviewRepository
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

        static::assertNotNull($this->repository->findOneByCommitHash((int)$repository->getId(), RevisionFixtures::COMMIT_HASH_A));
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
     * @covers \DR\Review\Repository\Review\CodeReviewQueryBuilder
     * @throws Exception
     */
    public function testGetPaginatorForSearchQuery(): void
    {
        $repository   = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $user         = Assert::notNull(static::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $repositoryId = (int)$repository->getId();

        $revisionRepository = static::getService(RevisionRepository::class);
        $revision           = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $review             = Assert::notNull($this->repository->findOneBy(['title' => 'title']));
        $reviewer           = new CodeReviewer();
        $reviewer->setStateTimestamp(1234);
        $reviewer->setReview($review);
        $reviewer->setUser($user);
        $review->getReviewers()->add($reviewer);

        $revision->setReview($review);
        $revisionRepository->save($revision, true);

        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, ''));

        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'id:' . $review->getProjectId()));
        static::assertCount(0, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'id:0'));

        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'state:closed'));
        static::assertCount(0, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'state:open'));

        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'author:me'));
        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'author:sherlock'));
        static::assertCount(0, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'author:watson'));

        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'reviewer:me'));
        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'reviewer:sherlock'));
        static::assertCount(0, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'reviewer:watson'));

        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'title'));
        static::assertCount(1, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, (string)$review->getProjectId()));
        static::assertCount(0, $this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, 'foobar'));
    }

    /**
     * @covers ::getPaginatorForSearchQuery
     * @covers \DR\Review\Repository\Review\CodeReviewQueryBuilder
     * @throws Exception
     */
    public function testGetPaginatorForSearchQueryOneReviewTwoRevisions(): void
    {
        $user = new User();
        $user->setEmail('sherlock@example.com');
        $repository   = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $repositoryId = (int)$repository->getId();

        $revisionRepository = static::getService(RevisionRepository::class);
        $revisionA          = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $revisionB          = Assert::notNull($revisionRepository->findOneBy(['title' => 'book']));
        $review             = Assert::notNull($this->repository->findOneBy(['title' => 'title']));

        $revisionA->setReview($review);
        $revisionB->setReview($review);
        $revisionRepository->save($revisionA, true);
        $revisionRepository->save($revisionB, true);

        $result = iterator_to_array($this->repository->getPaginatorForSearchQuery($user, $repositoryId, 1, ''));
        static::assertCount(1, $result);

        /** @var CodeReview $review */
        $review = Arrays::first($result);
        static::assertCount(2, $review->getRevisions());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class, RevisionFixtures::class];
    }
}
