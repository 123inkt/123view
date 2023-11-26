<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Factory;

use DR\Review\ApiPlatform\Factory\UserOutputFactory;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserOutputFactory::class)]
class UserOutputFactoryTest extends AbstractTestCase
{
    private UserOutputFactory $outputFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputFactory = new UserOutputFactory();
    }

    public function testCreate(): void
    {
        $user = new User();
        $user->setId(123);
        $user->setName('name');
        $user->setEmail('email');

        $output = $this->outputFactory->create($user);
        static::assertSame(123, $output->id);
        static::assertSame('name', $output->name);
        static::assertSame('email', $output->email);
    }
}
