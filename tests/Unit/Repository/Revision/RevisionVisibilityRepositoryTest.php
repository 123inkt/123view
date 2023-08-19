<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Revision;

use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Tests\DataFixtures\RevisionFixtures;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Utils\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\Review\Repository\Revision\RevisionVisibilityRepository
 * @covers ::__construct
 */
class RevisionVisibilityRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::saveAll
     * @covers ::removeAll
     * @throws Exception
     */
    public function testSaveAll(): void
    {
        $review   = Assert::notNull(self::getService(CodeReviewRepository::class)->findOneBy(['title' => 'title']));
        $revision = Assert::notNull(self::getService(RevisionRepository::class)->findOneBy(['title' => 'title']));
        $user     = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['name' => 'Sherlock Holmes']));

        $visibility = new RevisionVisibility();
        $visibility->setRevision($revision);
        $visibility->setReview($review);
        $visibility->setUser($user);
        $visibility->setVisible(false);

        // save
        $repository = self::getService(RevisionVisibilityRepository::class);
        $repository->saveAll([$visibility], true);

        // fetch
        static::assertCount(1, $repository->findAll());

        // remove
        $repository->removeAll([$visibility], true);
        static::assertCount(0, $repository->findAll());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CodeReviewFixtures::class, RevisionFixtures::class, UserFixtures::class];
    }
}
