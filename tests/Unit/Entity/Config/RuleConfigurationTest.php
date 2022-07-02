<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DateTime;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\RuleConfiguration
 */
class RuleConfigurationTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $dateA = new DateTime();
        $dateB = new DateTime();
        $rule  = new Rule();

        $config = new RuleConfiguration($dateA, $dateB, [], $rule);
        static::assertSame($dateA, $config->startTime);
        static::assertSame($dateB, $config->endTime);
        static::assertSame($rule, $config->rule);
    }
}
