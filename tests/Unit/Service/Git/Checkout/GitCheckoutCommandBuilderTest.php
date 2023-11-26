<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Checkout;

use DR\Review\Service\Git\Checkout\GitCheckoutCommandBuilder;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitCheckoutCommandBuilder::class)]
class GitCheckoutCommandBuilderTest extends AbstractTestCase
{
    private const DEFAULTS = ['git', 'checkout'];

    private GitCheckoutCommandBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GitCheckoutCommandBuilder('git');
    }

    public function testBuildDefaults(): void
    {
        static::assertSame(self::DEFAULTS, $this->builder->build());
    }

    public function testSetPath(): void
    {
        static::assertSame(['git', 'checkout', '-b branchName', 'point'], $this->builder->branch('branchName')->startPoint('point')->build());
    }

    public function testCommand(): void
    {
        static::assertSame('checkout', $this->builder->command());
    }

    public function testToString(): void
    {
        static::assertSame('git checkout -b branchName point', (string)$this->builder->branch('branchName')->startPoint('point'));
    }
}
