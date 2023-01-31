<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\User;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\User\User
 */
class UserTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getRules', 'addRule', 'getReviewers', 'getComments', 'getReplies', 'getMentions']);

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
     */
    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('email');
        static::assertSame('email', $user->getUserIdentifier());
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
