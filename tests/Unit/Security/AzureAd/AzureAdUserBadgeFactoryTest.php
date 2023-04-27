<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\AzureAd;

use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\AzureAd\AzureAdUserBadgeFactory;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Security\AzureAd\AzureAdUserBadgeFactory
 * @covers ::__construct
 */
class AzureAdUserBadgeFactoryTest extends AbstractTestCase
{
    private UserRepository&MockObject $userRepository;
    private AzureAdUserBadgeFactory   $factory;

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
        $this->userRepository->expects(self::once())->method('getUserCount')->willReturn(1);
        $this->userRepository->expects(self::once())->method('save')
            ->with(
                static::callback(static function (User $user): bool {
                    static::assertSame([], $user->getRoles());

                    return true;
                }),
                true
            );

        $badge = $this->factory->create('email', 'name');
        static::assertSame($user, $badge->getUser());
    }

    /**
     * @covers ::create
     */
    public function testCreateFirstNonExistingUser(): void
    {
        $user = new User();
        $this->userRepository->expects(self::exactly(2))->method('findOneBy')->with(['email' => 'email'])->willReturn(null, $user);
        $this->userRepository->expects(self::once())->method('getUserCount')->willReturn(0);
        $this->userRepository->expects(self::once())->method('save')
            ->with(
                static::callback(static function (User $user): bool {
                    static::assertSame([Roles::ROLE_USER, Roles::ROLE_ADMIN], $user->getRoles());

                    return true;
                }),
                true
            );

        $badge = $this->factory->create('email', 'name');
        static::assertSame($user, $badge->getUser());
    }
}
