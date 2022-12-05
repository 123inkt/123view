<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Validator\Filter;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Validator\Filter\IsValidPattern;
use Symfony\Component\Validator\Constraint;

/**
 * @coversDefaultClass \DR\Review\Validator\Filter\IsValidPattern
 */
class IsValidPatternTest extends AbstractTestCase
{
    /**
     * @covers ::getTargets
     */
    public function testGetTargets(): void
    {
        $constraint = new IsValidPattern();

        static::assertSame([Constraint::CLASS_CONSTRAINT], $constraint->getTargets());
    }
}
