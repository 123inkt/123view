<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @covers ::getReviewers
     * @covers ::setReviewers
     */
    public function testReviewers(): void
    {
        $collection = new ArrayCollection();

        $repository = new User();
        static::assertInstanceOf(ArrayCollection::class, $repository->getReviewers());

        $repository->setReviewers($collection);
        static::assertSame($collection, $repository->getReviewers());
    }

    /**
     * @covers ::getComments
     * @covers ::setComments
     */
    public function testComments(): void
    {
        $collection = new ArrayCollection();

        $repository = new User();
        static::assertInstanceOf(ArrayCollection::class, $repository->getComments());

        $repository->setComments($collection);
        static::assertSame($collection, $repository->getComments());
    }

    /**
     * @covers ::getReplies
     * @covers ::setReplies
     */
    public function testReplies(): void
    {
        $collection = new ArrayCollection();

        $repository = new User();
        static::assertInstanceOf(ArrayCollection::class, $repository->getReplies());

        $repository->setReplies($collection);
        static::assertSame($collection, $repository->getReplies());
    }
}
