<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\User
 */
class UserTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getRules', 'addRule']);

        static::assertNull((new User())->getId());
        static::assertAccessorPairs(User::class, $config);
    }

    /**
     * @covers ::addRule
     * @covers ::getRules
     * @covers ::removeRule
     */
    public function testRuleAccessors(): void
    {
        $rule = new Rule();

        $repository = new User();
        $repository->addRule($rule);
        static::assertSame([$rule], $repository->getRules()->getValues());

        $repository->removeRule($rule);
        static::assertCount(0, $repository->getRules());
    }

    /**
     * @covers ::getUserIdentifier
     * @covers ::getRoles
     */
    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('email');
        static::assertSame('email', $user->getUserIdentifier());
        static::assertSame(['ROLE_USER'], $user->getRoles());
    }
}
