<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Validator\Filter;

use DR\GitCommitNotification\Validator\Filter\IsValidPattern;
use DR\GitCommitNotification\Tests\AbstractTest;
use Symfony\Component\Validator\Constraint;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Validator\Filter\IsValidPattern
 */
class IsValidPatternTest extends AbstractTest
{
    /**
     * @covers ::getTargets
     */
    public function testGetTargets(): void
    {
        $constraint = new IsValidPattern();

        static::assertSame([[Constraint::CLASS_CONSTRAINT]], $constraint->getTargets());
    }
}
