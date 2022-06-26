<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\AzureAd;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\Repository\UserRepository;
use DR\GitCommitNotification\Security\AzureAd\AzureAdUserBadge;
use DR\GitCommitNotification\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\AzureAd\AzureAdUserBadge
 * @covers ::__construct
 */
class AzureAdUserBadgeTest extends AbstractTest
{
    /** @var UserRepository&MockObject */
    private UserRepository $userRepository;
    /** @var MockObject&ManagerRegistry */
    private ManagerRegistry  $doctrine;
    private AzureAdUserBadge $badge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->doctrine       = $this->createMock(ManagerRegistry::class);
        $this->doctrine->method('getRepository')->with(User::class)->willReturn($this->userRepository);
        $this->badge = new AzureAdUserBadge($this->doctrine, 'email', 'name');
    }

    /**
     * @covers ::__construct
     * @covers ::fetchOrCreateUser
     */
    public function testReturnNonExistingUser(): void
    {
        $user = new User();
        $this->userRepository->expects(self::exactly(2))->method('findOneBy')->with(['email' => 'email'])->willReturn(null, $user);
        $this->userRepository->expects(self::once())->method('add')->with(static::isInstanceOf(User::class), true);

        static::assertSame($user, $this->badge->getUser());
    }

    /**
     * @covers ::__construct
     * @covers ::fetchOrCreateUser
     */
    public function testReturnExistingUser(): void
    {
        $user = new User();
        $this->userRepository->expects(self::once())->method('findOneBy')->with(['email' => 'email'])->willReturn($user);
        $this->userRepository->expects(self::never())->method('add');

        static::assertSame($user, $this->badge->getUser());
    }
}
