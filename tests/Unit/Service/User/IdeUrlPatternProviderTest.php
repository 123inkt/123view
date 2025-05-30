<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\User;

use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Service\User\IdeUrlPatternProvider;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(IdeUrlPatternProvider::class)]
class IdeUrlPatternProviderTest extends AbstractTestCase
{
    private UserEntityProvider&MockObject $userEntityProvider;
    private IdeUrlPatternProvider         $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userEntityProvider = $this->createMock(UserEntityProvider::class);
        $this->provider           = new IdeUrlPatternProvider('url-pattern', $this->userEntityProvider);
    }

    public function testGetUrlWithoutUser(): void
    {
        $this->userEntityProvider->expects($this->once())->method('getUser')->willReturn(null);
        self::assertSame('url-pattern', $this->provider->getUrl());
    }

    public function testGetUrlWithUser(): void
    {
        $setting = (new UserSetting())->setIdeUrl('user-ide-url');
        $user    = (new User())->setSetting($setting);

        $this->userEntityProvider->expects($this->once())->method('getUser')->willReturn($user);
        self::assertSame('user-ide-url', $this->provider->getUrl());
    }
}
