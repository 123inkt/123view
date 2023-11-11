<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Entity\Notification\Filter;
use DR\Review\Entity\Notification\Recipient;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Notification\Rule
 * @covers ::__construct
 */
class RuleTest extends AbstractTestCase
{
    /**
     * @covers ::setId
     * @covers ::getId
     * @covers ::setName
     * @covers ::getName
     * @covers ::setUser
     * @covers ::getUser
     * @covers ::setRuleOptions
     * @covers ::getRuleOptions
     * @covers ::setActive
     * @covers ::isActive
     */
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getRepositories', 'addRepository', 'getRecipients', 'addRecipient', 'getFilters', 'addFilter']);

        static::assertAccessorPairs(Rule::class, $config);
    }

    /**
     * @covers ::getRepositories
     * @covers ::addRepository
     * @covers ::removeRepository
     */
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

    /**
     * @covers ::getRecipients
     * @covers ::addRecipient
     * @covers ::removeRecipient
     */
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

    /**
     * @covers ::getFilters
     * @covers ::addFilter
     * @covers ::removeFilter
     */
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
