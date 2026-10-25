<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Mcp;

use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Model\Mcp\CodeReviewQuery;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Tests\DataFixtures\RevisionFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewRepository::class)]
class CodeReviewRepositoryTest extends AbstractRepositoryTestCase
{
    private CodeReviewRepository $repository;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->repository = static::getService(CodeReviewRepository::class);

        // Link the fixture revision to the fixture review
        $revisionRepository = static::getService(RevisionRepository::class);
        $revision           = Assert::notNull($revisionRepository->findOneBy(['commitHash' => RevisionFixtures::COMMIT_HASH_A]));
        $review             = Assert::notNull($this->repository->findOneBy(['title' => 'title']));
        $revision->setReview($review);
        $revisionRepository->save($revision, true);
    }

    /**
     * @throws Exception
     */
    public function testFindByFiltersWithNoFilters(): void
    {
        $results = $this->repository->findByFilters(new CodeReviewQuery(), 50);

        static::assertCount(1, $results);
        static::assertSame('title', $results[0]->getTitle());
    }

    /**
     * @throws Exception
     */
    public function testFindByFiltersWithAllFilters(): void
    {
        $query = new CodeReviewQuery(
            title:         'itle',
            branchName:    'first-branch',
            authorEmail:   'sherlock@example.com',
            repositoryUrl: 'url',
            state:         CodeReviewStateType::CLOSED,
        );

        $results = $this->repository->findByFilters($query, 50);

        static::assertCount(1, $results);
        static::assertSame('title', $results[0]->getTitle());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CodeReviewFixtures::class, RevisionFixtures::class];
    }
}
