<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\GitCommitNotification\Entity\Config\Filter;
use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Rule
 * @covers ::__construct
 */
class RuleTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getRepositories', 'addRepository', 'getRecipients', 'addRecipient', 'getFilters', 'addFilter']);

        static::assertNull((new Rule())->getId());
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
