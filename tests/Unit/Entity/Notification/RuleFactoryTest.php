<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Notification;

use DR\GitCommitNotification\Entity\Notification\RuleFactory;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Notification\RuleFactory
 */
class RuleFactoryTest extends AbstractTestCase
{
    /**
     * @covers ::createDefault
     */
    public function testCreateDefault(): void
    {
        $user = new User();
        $user->setName('name');
        $user->setEmail('email');
        $rule = RuleFactory::createDefault($user);

        static::assertSame($user, $rule->getUser());
        static::assertNotNull($rule->getRuleOptions());
        static::assertCount(1, $rule->getFilters());
        static::assertCount(1, $rule->getRecipients());
    }
}
