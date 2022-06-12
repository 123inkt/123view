<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Validator\Filter;

use DR\GitCommitNotification\Validator\Filter\IsValidPattern;
use DR\GitCommitNotification\Validator\Filter\IsValidPatternValidator;
use RuntimeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Validator\Filter\IsValidPatternValidator
 * @covers ::__construct
 */
class IsValidPatternValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @covers ::validate
     */
    public function testValidateShouldOnlyAcceptFilter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only type Filter is valid');
        $this->validator->validate(null, new IsValidPattern());
    }

    protected function createValidator(): IsValidPatternValidator
    {
        return new IsValidPatternValidator();
    }
}
