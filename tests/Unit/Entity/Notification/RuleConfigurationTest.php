<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DatePeriod;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Notification\RuleConfiguration
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
