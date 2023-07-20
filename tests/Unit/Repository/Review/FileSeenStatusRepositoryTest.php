<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Review\FileSeenStatus;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\FileSeenStatusRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Utils\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\Review\Repository\Review\FileSeenStatusRepository
 * @covers ::__construct
 */
class FileSeenStatusRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::save
     * @throws Exception
     */
    public function testSave(): void
    {
        $user             = Assert::notNull(static::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $review           = Assert::notNull(static::getService(CodeReviewRepository::class)->findOneBy(['title' => 'title']));
        $statusRepository = static::getService(FileSeenStatusRepository::class);

        $statusA = new FileSeenStatus();
        $statusA->setFilePath('filepath');
        $statusA->setReview($review);
        $statusA->setUser($user);
        $statusA->setCreateTimestamp(123456789);
        $statusRepository->save($statusA, true);
        static::assertNotNull($statusA->getId());

        // seen status with same primary key, should not save
        $statusB = new FileSeenStatus();
        $statusB->setFilePath('filepath');
        $statusB->setReview($review);
        $statusB->setUser($user);
        $statusB->setCreateTimestamp(123456789);
        $statusRepository->save($statusB, true);
        static::assertNull($statusB->getId());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class];
    }
}
