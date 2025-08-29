<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\User;

use DR\Review\Entity\User\User;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[CoversClass(UserEntityProvider::class)]
class UserEntityProviderTest extends AbstractTestCase
{
    private TokenStorageInterface&MockObject $tokenStore;
    private Security&MockObject              $security;
    private UserEntityProvider               $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenStore = $this->createMock(TokenStorageInterface::class);
        $this->security   = $this->createMock(Security::class);
        $this->provider   = new UserEntityProvider($this->tokenStore, $this->security);
    }

    public function testGetCurrentUser(): void
    {
        $user = new User();
        $this->security->expects($this->once())->method('getUser')->willReturn($user);

        static::assertSame($user, $this->provider->getCurrentUser());
    }

    public function testGetUserExisting(): void
    {
        $user  = new User();
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $this->tokenStore->expects($this->once())->method('getToken')->willReturn($token);

        static::assertSame($user, $this->provider->getUser());
    }

    public function testGetUserNonExisting(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn(null);

        $this->tokenStore->expects($this->once())->method('getToken')->willReturn($token);

        static::assertNull($this->provider->getUser());
    }

    public function testGetUserNoToken(): void
    {
        $this->tokenStore->expects($this->once())->method('getToken')->willReturn(null);

        static::assertNull($this->provider->getUser());
    }
}
