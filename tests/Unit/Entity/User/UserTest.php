<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\User;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Entity\User\UserSetting;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\User\User
 */
class UserTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getRules', 'addRule', 'getReviewers', 'getComments', 'getReplies']);

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

    /**
     * @covers ::setSetting
     * @covers ::getSetting
     */
    public function testGetSetting(): void
    {
        $setting = new UserSetting();
        $user    = new User();
        $user->setSetting($setting);
        static::assertSame($setting, $user->getSetting());
    }

    /**
     * @covers ::getReviewers
     * @covers ::setReviewers
     */
    public function testReviewers(): void
    {
        $collection = new ArrayCollection();

        $user = new User();
        static::assertInstanceOf(ArrayCollection::class, $user->getReviewers());

        $user->setReviewers($collection);
        static::assertSame($collection, $user->getReviewers());
    }

    /**
     * @covers ::getComments
     * @covers ::setComments
     */
    public function testComments(): void
    {
        $collection = new ArrayCollection();

        $user = new User();
        static::assertInstanceOf(ArrayCollection::class, $user->getComments());

        $user->setComments($collection);
        static::assertSame($collection, $user->getComments());
    }

    /**
     * @covers ::getReplies
     * @covers ::setReplies
     */
    public function testReplies(): void
    {
        $collection = new ArrayCollection();

        $user = new User();
        static::assertInstanceOf(ArrayCollection::class, $user->getReplies());

        $user->setReplies($collection);
        static::assertSame($collection, $user->getReplies());
    }
}
