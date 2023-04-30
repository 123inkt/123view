<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Doctrine\EntityRepository;

use DR\Review\Doctrine\EntityRepository\ServiceEntityRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Review\Utility\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ServiceEntityRepository::class)]
class ServiceEntityRepositoryTest extends AbstractRepositoryTestCase
{
    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = static::getService(UserRepository::class);
    }

    public function testRemoveAndSave(): void
    {
        $user = Assert::notNull($this->userRepository->findOneBy(['email' => 'sherlock@example.com']));

        // remove user
        $this->userRepository->remove($user, true);
        static::assertNull($this->userRepository->findOneBy(['email' => 'sherlock@example.com']));

        $user->setId(null);
        $this->userRepository->save($user, true);

        static::assertNotNull($this->userRepository->findOneBy(['email' => 'sherlock@example.com']));
    }

    public function testRemoveOneBy(): void
    {
        static::assertNotNull($this->userRepository->findOneBy(['email' => 'sherlock@example.com']));

        // remove user
        $this->userRepository->removeOneBy(['email' => 'sherlock@example.com'], null, true);
        static::assertNull($this->userRepository->findOneBy(['email' => 'sherlock@example.com']));

        // should skip removal of user
        $this->userRepository->removeOneBy(['email' => 'sherlock@example.com'], null, true);
        static::assertNull($this->userRepository->findOneBy(['email' => 'sherlock@example.com']));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class];
    }
}
