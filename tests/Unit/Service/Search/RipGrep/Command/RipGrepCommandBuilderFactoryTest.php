<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep\Command;

use DR\Review\Service\Search\RipGrep\Command\RipGrepCommandBuilder;
use DR\Review\Service\Search\RipGrep\Command\RipGrepCommandBuilderFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RipGrepCommandBuilderFactory::class)]
class RipGrepCommandBuilderFactoryTest extends AbstractTestCase
{
    private RipGrepCommandBuilderFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new RipGrepCommandBuilderFactory();
    }

    public function testDefault(): void
    {
        $expected = (new RipGrepCommandBuilder())
            ->hidden()
            ->noColor()
            ->lineNumber()
            ->beforeContext(5)
            ->afterContext(5)
            ->glob('!.git/')
            ->json();

        $result = $this->factory->default();
        self::assertEquals($expected, $result);
    }
}
