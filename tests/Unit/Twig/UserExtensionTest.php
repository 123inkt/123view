<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\UserExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(UserExtension::class)]
class UserExtensionTest extends AbstractTestCase
{
    private UserRepository&MockObject $userRepository;
    private UserExtension             $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->extension      = new UserExtension($this->userRepository);
    }

    /**
     * @throws Throwable
     */
    public function testGetUserCount(): void
    {
        $this->userRepository->expects($this->once())->method('getNewUserCount')->willReturn(5);
        static::assertSame(5, $this->extension->getUserCount());
    }
}
