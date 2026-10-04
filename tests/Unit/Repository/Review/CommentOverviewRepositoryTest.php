<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentOverviewRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CommentReplyApiFixtures;
use DR\Review\ViewModel\App\Comment\CommentOverviewItem;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentOverviewRepository::class)]
class CommentOverviewRepositoryTest extends AbstractRepositoryTestCase
{
    public function testGetByUser(): void
    {
        $user = $this->getSherlock();
        $repository = static::getService(CommentOverviewRepository::class);

        $result = $repository->getByUser($user, 1, '', CommentOverviewRepository::ORDER_CREATE_TIMESTAMP);

        static::assertSame(3, $result['total']);
        static::assertCount(3, $result['items']);
        static::assertContainsOnlyInstancesOf(CommentOverviewItem::class, $result['items']);
        static::assertCount(2, array_filter($result['items'], static fn(CommentOverviewItem $item): bool => $item->isReply === false));
        static::assertCount(1, array_filter($result['items'], static fn(CommentOverviewItem $item): bool => $item->isReply));
    }

    public function testGetByUserWithSearchAndUpdateOrder(): void
    {
        $user = $this->getSherlock();
        $repository = static::getService(CommentOverviewRepository::class);

        $result = $repository->getByUser(
            $user,
            1,
            CommentReplyApiFixtures::FINAL_REPLY_ONE,
            CommentOverviewRepository::ORDER_UPDATE_TIMESTAMP
        );

        static::assertSame(1, $result['total']);
        static::assertCount(1, $result['items']);
        static::assertTrue($result['items'][0]->isReply);
        static::assertSame(CommentReplyApiFixtures::FINAL_REPLY_ONE, $result['items'][0]->content->getMessage());
    }

    public function testGetByUserWithoutResults(): void
    {
        $user = new User()->setId(999999);
        $repository = static::getService(CommentOverviewRepository::class);

        $result = $repository->getByUser($user, 2, 'not found', CommentOverviewRepository::ORDER_CREATE_TIMESTAMP);

        static::assertSame(0, $result['total']);
        static::assertSame([], $result['items']);
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [CommentReplyApiFixtures::class];
    }

    private function getSherlock(): User
    {
        $userRepository = static::getService(UserRepository::class);

        return Assert::notNull($userRepository->findOneBy(['email' => 'sherlock@example.com']));
    }
}
