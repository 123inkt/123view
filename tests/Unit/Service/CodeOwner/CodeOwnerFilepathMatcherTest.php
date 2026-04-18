<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Review\Service\CodeOwner\CodeOwnerFilepathMatcher;
use DR\Review\Service\CodeOwner\CodeOwnerPatternMatcher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeOwnerFilepathMatcher::class)]
class CodeOwnerFilepathMatcherTest extends AbstractTestCase
{
    private CodeOwnerPatternMatcher&MockObject $matcher;
    private CodeOwnerFilepathMatcher           $filepath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher  = $this->createMock(CodeOwnerPatternMatcher::class);
        $this->filepath = new CodeOwnerFilepathMatcher($this->matcher);
    }

    public function testMatch(): void
    {
        $patternA = new OwnerPattern('*.js', ['@frontend']);
        $patternB = new OwnerPattern('*.ts', ['@backend']);

        $this->matcher->expects($this->exactly(2))
            ->method('match')
            ->willReturnMap([['foo.ts', $patternA, false], ['foo.ts', $patternB, true],]);

        static::assertSame($patternB, $this->filepath->match('foo.ts', [$patternA, $patternB]));
        static::assertNull($this->filepath->match('foo.ts', []));
    }
}
