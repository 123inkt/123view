<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\AzureAd;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
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
    /** @var MockObject&ObjectManager */
    private ObjectManager $objectManager;
    /** @var MockObject&ManagerRegistry */
    private ManagerRegistry  $doctrine;
    private AzureAdUserBadge $badge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->objectManager  = $this->createMock(ObjectManager::class);
        $this->doctrine       = $this->createMock(ManagerRegistry::class);
        $this->doctrine->method('getRepository')->with(User::class)->willReturn($this->userRepository);
        $this->doctrine->method('getManager')->willReturn($this->objectManager);
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
        $this->objectManager->expects(self::once())->method('persist')->with(static::isInstanceOf(User::class));
        $this->objectManager->expects(self::once())->method('flush');

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
        $this->objectManager->expects(self::never())->method('persist');
        $this->objectManager->expects(self::never())->method('flush');

        static::assertSame($user, $this->badge->getUser());
    }
}
