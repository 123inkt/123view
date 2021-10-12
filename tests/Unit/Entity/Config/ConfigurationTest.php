<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\Configuration;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Configuration
 */
class ConfigurationTest extends AbstractTest
{
    /**
     * @covers ::getRules
     * @covers ::addRule
     */
    public function testRules(): void
    {
        $config = new Configuration();
        $rule   = new Rule();

        static::assertEmpty($config->getRules());

        $config->addRule($rule);
        static::assertSame([$rule], $config->getRules());
        static::assertSame($config, $rule->config);
    }
}
