<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Review\FolderCollapseStatus;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\FolderCollapseStatusRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Utils\Assert;
use Error;
use Exception;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FolderCollapseStatusRepository::class)]
class FolderCollapseStatusRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testSave(): void
    {
        $user             = Assert::notNull(static::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $review           = Assert::notNull(static::getService(CodeReviewRepository::class)->findOneBy(['title' => 'title']));
        $statusRepository = static::getService(FolderCollapseStatusRepository::class);

        $statusA = new FolderCollapseStatus();
        $statusA->setPath('path');
        $statusA->setReview($review);
        $statusA->setUser($user);
        $statusRepository->save($statusA, true);
        static::assertNotNull($statusA->getId());

        // status with same primary key, should not save
        $statusB = new FolderCollapseStatus();
        $statusB->setPath('path');
        $statusB->setReview($review);
        $statusB->setUser($user);
        $statusRepository->save($statusB, true);

        $this->expectException(Error::class);
        $statusB->getId();
    }

    #[Override]
    protected function getFixtures(): array
    {
        return [UserFixtures::class, CodeReviewFixtures::class];
    }
}
