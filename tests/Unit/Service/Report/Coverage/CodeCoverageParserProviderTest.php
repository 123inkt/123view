<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\Coverage;

use ArrayIterator;
use DR\Review\Service\Report\Coverage\CodeCoverageParserProvider;
use DR\Review\Service\Report\Coverage\Parser\CodeCoverageParserInterface;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;

#[CoversClass(CodeCoverageParserProvider::class)]
class CodeCoverageParserProviderTest extends AbstractTestCase
{
    private CodeCoverageParserProvider             $provider;
    private CodeCoverageParserInterface&Stub $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = static::createStub(CodeCoverageParserInterface::class);
        $iterator     = new ArrayIterator(['parser' => $this->parser]);

        $this->provider = new CodeCoverageParserProvider($iterator);
    }

    public function testGetParser(): void
    {
        static::assertSame($this->parser, $this->provider->getParser('parser'));
    }

    public function testGetParserInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown coverage format');
        $this->provider->getParser('foobar');
    }
}
