<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Notification;

use DatePeriod;
use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Entity\Notification\RuleConfiguration;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Notification\RuleConfiguration
 */
class RuleConfigurationTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $period = $this->createMock(DatePeriod::class);
        $rule   = new Rule();

        $config = new RuleConfiguration($period, $rule);
        static::assertSame($period, $config->period);
        static::assertSame($rule, $config->rule);
    }
}
