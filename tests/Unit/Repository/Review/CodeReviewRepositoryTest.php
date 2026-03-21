<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;
use DR\Review\QueryParser\Term\EmptyMatch;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewQueryBuilder as QueryBuilder;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryParserFactory;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Tests\DataFixtures\RevisionFixtures;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Exception;
use Parsica\Parsica\Parser;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewRepository::class)]
#[CoversClass(QueryBuilder::class)]
class CodeReviewRepositoryTest extends AbstractRepositoryTestCase
{
    private CodeReviewRepository $repository;

    private User $user;
    /** @var Parser<TermInterface> */
    private Parser $parser;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = Assert::notNull(static::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        self::getContainer()->set(User::class, $this->user);

        $this->repository = static::getService(CodeReviewRepository::class);
        $this->parser     = (new ReviewSearchQueryParserFactory())->createParser();
    }

    /**
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
     * @throws Exception
     */
    public function testFindOneByReferenceId(): void
    {
        $repository = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));

        static::assertNotNull($this->repository->findOneByReferenceId((int)$repository->getId(), 'reference', CodeReviewType::COMMITS));
    }

    /**
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
     * @throws Exception
     */
    public function testFindByBranchName(): void
    {
        $revisionRepository = static::getService(RevisionRepository::class);

        $repository = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $revision   = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $review     = Assert::notNull($this->repository->findOneBy(['title' => 'title']));

        $revision->setReview($review);
        $revisionRepository->save($revision, true);

        static::assertSame([$review], $this->repository->findByBranchName((int)$repository->getId(), 'first-branch'));
    }

    /**
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
     * @throws Exception
     */
    public function testGetPaginatorForSearchQuery(): void
    {
        $repository   = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $repositoryId = (int)$repository->getId();

        $revisionRepository = static::getService(RevisionRepository::class);
        $revision           = Assert::notNull($revisionRepository->findOneBy(['title' => 'title']));
        $review             = Assert::notNull($this->repository->findOneBy(['title' => 'title']));
        $reviewer           = new CodeReviewer();
        $reviewer->setStateTimestamp(1234);
        $reviewer->setReview($review);
        $reviewer->setUser($this->user);
        $review->getReviewers()->add($reviewer);

        $revision->setReview($review);
        $revisionRepository->save($revision, true);

        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, ''));
        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, '', QueryBuilder::ORDER_CREATE_TIMESTAMP));

        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, 'id:' . $review->getProjectId()));
        static::assertCount(0, $this->getPaginatorForSearchQuery($repositoryId, 'id:0'));

        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, 'state:closed'));
        static::assertCount(0, $this->getPaginatorForSearchQuery($repositoryId, 'state:open'));

        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, 'author:me'));
        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, 'author:sherlock'));
        static::assertCount(0, $this->getPaginatorForSearchQuery($repositoryId, 'author:watson'));

        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, 'reviewer:me'));
        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, 'reviewer:sherlock'));
        static::assertCount(0, $this->getPaginatorForSearchQuery($repositoryId, 'reviewer:watson'));

        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, 'title'));
        static::assertCount(1, $this->getPaginatorForSearchQuery($repositoryId, (string)$review->getProjectId()));
        static::assertCount(0, $this->getPaginatorForSearchQuery($repositoryId, 'foobar'));
    }

    /**
     * @throws Exception
     */
    public function testGetPaginatorForSearchQueryOneReviewTwoRevisions(): void
    {
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

        $result = iterator_to_array($this->getPaginatorForSearchQuery($repositoryId, ''));
        static::assertCount(1, $result);

        /** @var CodeReview $review */
        $review = Arrays::first($result);
        static::assertCount(2, $review->getRevisions());
    }

    /**
     * @throws Exception
     */
    public function testFindByTitle(): void
    {
        $repository = Assert::notNull(static::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));

        // Get the existing review with title 'title'
        $existingReview = Assert::notNull($this->repository->findOneBy(['title' => 'title']));

        $this->createAdditionalReviews($repository);

        // Test findByTitle
        $results = $this->repository->findByTitle($existingReview);

        // Should return both reviews with title 'title', ordered by repository name
        static::assertCount(2, $results);

        // Verify all results have the same title
        foreach ($results as $result) {
            static::assertSame('title', $result->getTitle());
        }
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class, RevisionFixtures::class];
    }

    /**
     * @return Paginator<CodeReview>
     * @throws Exception
     */
    private function getPaginatorForSearchQuery(int $repositoryId, string $query, string $orderBy = QueryBuilder::ORDER_UPDATE_TIMESTAMP): Paginator
    {
        $terms = $query === '' ? new EmptyMatch() : $this->parser->tryString($query)->output();

        return $this->repository->getPaginatorForSearchQuery($repositoryId, 1, $terms, $orderBy);
    }

    private function createAdditionalReviews(Repository $repository): void
    {
        // Create a second review with the same title
        $review2 = new CodeReview();
        $review2->setProjectId(7328);
        $review2->setTitle('title');
        $review2->setDescription('another review with same title');
        $review2->setCreateTimestamp(12346790);
        $review2->setUpdateTimestamp(12346790);
        $review2->setRepository($repository);
        $this->repository->save($review2, true);

        // Create a review with different title to ensure it's not included
        $review3 = new CodeReview();
        $review3->setProjectId(7330);
        $review3->setTitle('different title');
        $review3->setDescription('review with different title');
        $review3->setCreateTimestamp(12346792);
        $review3->setUpdateTimestamp(12346792);
        $review3->setRepository($repository);
        $this->repository->save($review3, true);
    }
}
