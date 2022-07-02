<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\AzureAd;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use DR\GitCommitNotification\Security\AzureAd\AzureAdUserBadgeFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\AzureAd\AzureAdUserBadgeFactory
 * @covers ::__construct
 */
class AzureAdUserBadgeFactoryTest extends AbstractTestCase
{
    /** @var MockObject&UserRepository */
    private UserRepository          $userRepository;
    private AzureAdUserBadgeFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->factory        = new AzureAdUserBadgeFactory($this->userRepository);
    }

    /**
     * @covers ::create
     */
    public function testCreateExistingUser(): void
    {
        $user = new User();
        $this->userRepository->expects(self::once())->method('findOneBy')->with(['email' => 'email'])->willReturn($user);

        $badge = $this->factory->create('email', 'name');
        static::assertSame($user, $badge->getUser());
    }

    /**
     * @covers ::create
     */
    public function testCreateNonExistingUser(): void
    {
        $user = new User();
        $this->userRepository->expects(self::exactly(2))->method('findOneBy')->with(['email' => 'email'])->willReturn(null, $user);
        $this->userRepository->expects(self::once())->method('add')->with(static::isInstanceOf(User::class), true);

        $badge = $this->factory->create('email', 'name');
        static::assertSame($user, $badge->getUser());
    }
}
