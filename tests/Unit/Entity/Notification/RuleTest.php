<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Entity\Notification\Filter;
use DR\Review\Entity\Notification\Recipient;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Rule::class)]
class RuleTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getRepositories', 'addRepository', 'getRecipients', 'addRecipient', 'getFilters', 'addFilter']);

        static::assertAccessorPairs(Rule::class, $config);
    }

    public function testGetRepositories(): void
    {
        $rule = new Rule();
        static::assertCount(0, $rule->getRepositories());

        $repository = new Repository();
        $rule->addRepository($repository);
        static::assertSame([$repository], iterator_to_array($rule->getRepositories()));

        $rule->removeRepository($repository);
        static::assertCount(0, $rule->getRepositories());
    }

    public function testGetRecipients(): void
    {
        $rule = new Rule();
        static::assertCount(0, $rule->getRecipients());

        $recipient = new Recipient();
        $rule->addRecipient($recipient);
        static::assertSame([$recipient], iterator_to_array($rule->getRecipients()));

        $rule->removeRecipient($recipient);
        static::assertCount(0, $rule->getRecipients());
    }

    public function testGetFilters(): void
    {
        $rule = new Rule();
        static::assertCount(0, $rule->getFilters());

        $filter = new Filter();
        $rule->addFilter($filter);
        static::assertSame([$filter], iterator_to_array($rule->getFilters()));

        $rule->removeFilter($filter);
        static::assertCount(0, $rule->getFilters());
    }
}
