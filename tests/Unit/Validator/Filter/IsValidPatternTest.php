<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Validator\Filter;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Validator\Filter\IsValidPattern;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Constraint;

#[CoversClass(IsValidPattern::class)]
class IsValidPatternTest extends AbstractTestCase
{
    public function testGetTargets(): void
    {
        $constraint = new IsValidPattern();

        static::assertSame([Constraint::CLASS_CONSTRAINT], $constraint->getTargets());
    }
}
