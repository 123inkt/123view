<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\UserExtension;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\UserExtension
 * @covers ::__construct
 */
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
     * @covers ::getUserCount
     * @throws Throwable
     */
    public function testGetUserCount(): void
    {
        $this->userRepository->expects(self::once())->method('getNewUserCount')->willReturn(5);
        static::assertSame(5, $this->extension->getUserCount());
    }

    /**
     * @covers ::getFunctions
     */
    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        static::assertCount(1, $functions);

        $function = $functions[0];
        static::assertSame('new_user_count', $function->getName());
    }
}
