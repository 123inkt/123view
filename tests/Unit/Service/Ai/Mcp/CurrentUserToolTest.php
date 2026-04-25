<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\User\User;
use DR\Review\Service\Ai\Mcp\CurrentUserTool;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CurrentUserTool::class)]
class CurrentUserToolTest extends AbstractTestCase
{
    private UserEntityProvider&MockObject $userEntityProvider;
    private CurrentUserTool               $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->userEntityProvider = $this->createMock(UserEntityProvider::class);
        $this->tool               = new CurrentUserTool($this->userEntityProvider);
    }

    public function testInvokeReturnsCurrentUserData(): void
    {
        $user = new User()->setId(42)->setName('John Doe')->setEmail('john@example.com');

        $this->userEntityProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);

        $result = ($this->tool)();

        static::assertSame(['id' => 42, 'name' => 'John Doe', 'email' => 'john@example.com'], $result);
    }
}
