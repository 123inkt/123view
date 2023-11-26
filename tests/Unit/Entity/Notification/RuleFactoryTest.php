<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\RuleFactory;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RuleFactory::class)]
class RuleFactoryTest extends AbstractTestCase
{
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
