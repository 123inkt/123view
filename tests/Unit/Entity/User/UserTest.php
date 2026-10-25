<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\User;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getRules', 'addRule', 'getReviewers', 'getComments', 'getReplies', 'getMentions', 'getGitAccessTokens']);

        static::assertAccessorPairs(User::class, $config);
    }

    public function testHasId(): void
    {
        $user = new User();
        static::assertFalse($user->hasId());

        $user->setId(123);
        static::assertTrue($user->hasId());
    }

    public function testHasRole(): void
    {
        $user = new User();
        $user->setRoles(['foo']);
        static::assertTrue($user->hasRole('foo'));
        static::assertFalse($user->hasRole('bar'));
    }

    public function testRuleAccessors(): void
    {
        $rule = new Rule();

        $user = new User();
        $user->addRule($rule);
        static::assertSame([$rule], $user->getRules()->getValues());

        $user->removeRule($rule);
        static::assertCount(0, $user->getRules());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('email');
        static::assertSame('email', $user->getUserIdentifier());
    }

    public function testGetSetting(): void
    {
        $setting = new UserSetting();
        $user    = new User();
        $user->setSetting($setting);
        static::assertSame($setting, $user->getSetting());
    }

    public function testGetReviewSetting(): void
    {
        $setting = new UserReviewSetting();
        $user = new User()->setReviewSetting($setting);

        static::assertSame($setting, $user->getReviewSetting());
    }

    public function testGetReviewSettingCreatesInstanceIfNull(): void
    {
        $user          = new User();
        $reviewSetting = $user->getReviewSetting();
        static::assertSame($user, $reviewSetting->getUser());
    }

    public function testGitAccessTokens(): void
    {
        $collection = new ArrayCollection();

        $user = new User();
        static::assertInstanceOf(ArrayCollection::class, $user->getGitAccessTokens());

        $user->setGitAccessTokens($collection);
        static::assertSame($collection, $user->getGitAccessTokens());
    }

    public function testReviewers(): void
    {
        $collection = new ArrayCollection();

        $user = new User();
        static::assertInstanceOf(ArrayCollection::class, $user->getReviewers());

        $user->setReviewers($collection);
        static::assertSame($collection, $user->getReviewers());
    }

    public function testComments(): void
    {
        $collection = new ArrayCollection();

        $user = new User();
        static::assertInstanceOf(ArrayCollection::class, $user->getComments());

        $user->setComments($collection);
        static::assertSame($collection, $user->getComments());
    }

    public function testReplies(): void
    {
        $collection = new ArrayCollection();

        $user = new User();
        static::assertInstanceOf(ArrayCollection::class, $user->getReplies());

        $user->setReplies($collection);
        static::assertSame($collection, $user->getReplies());
    }

    public function testEqualsTo(): void
    {
        $userA = new User();
        $userA->setId(123);
        $userB = new User();
        $userB->setId(456);
        $userC = new User();
        $userC->setId(123);

        static::assertTrue($userA->equalsTo($userA));
        static::assertFalse($userA->equalsTo($userB));
        static::assertTrue($userA->equalsTo($userC));
        static::assertFalse($userA->equalsTo("foobar"));
    }

    public function testCompareTo(): void
    {
        $userA = new User();
        $userA->setId(123);
        $userB = new User();
        $userB->setId(456);
        $userC = new User();
        $userC->setId(123);

        static::assertSame(0, $userA->compareTo($userA));
        static::assertSame(-1, $userA->compareTo($userB));
        static::assertSame(1, $userB->compareTo($userA));
        static::assertSame(0, $userA->compareTo($userC));
        static::assertSame(-1, $userA->compareTo("foobar"));
    }
}
