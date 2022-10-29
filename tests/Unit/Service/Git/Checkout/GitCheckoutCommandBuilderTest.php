<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Checkout;

use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutCommandBuilder;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutCommandBuilder
 * @covers ::__construct
 */
class GitCheckoutCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'checkout'];

    private GitCheckoutCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitCheckoutCommandBuilder('git');
    }

    /**
     * @covers ::build
     */
    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    /**
     * @covers ::branch
     * @covers ::startPoint
     * @covers ::build
     */
    public function testSetPath(): void
    {
        static::assertSame(['git', 'checkout', '-b branchName', 'point'], $this->builder->branch('branchName')->startPoint('point')->build());
    }

    /**
     * @covers ::command
     */
    public function testCommand(): void
    {
        static::assertSame('checkout', $this->builder->command());
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        static::assertSame('git checkout -b branchName point', (string)$this->builder->branch('branchName')->startPoint('point'));
    }
}
