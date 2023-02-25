<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Validator\Filter;

use DR\Review\Doctrine\Type\FilterType as EntityFilterType;
use DR\Review\Entity\Notification\Filter;
use DR\Review\Validator\Filter\IsValidPattern;
use DR\Review\Validator\Filter\IsValidPatternValidator;
use RuntimeException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @coversDefaultClass \DR\Review\Validator\Filter\IsValidPatternValidator
 * @extends ConstraintValidatorTestCase<IsValidPatternValidator>
 */
class IsValidPatternValidatorTest extends ConstraintValidatorTestCase
{
    protected function setUp(): void
    {
        if (Kernel::VERSION_ID < 60300) {
            self::markTestSkipped('Symfony 6.2 and lower is not compatible with PHPUnit 10.');
        }
        parent::setUp();
    }

    /**
     * @covers ::validate
     */
    public function testValidateShouldOnlyAcceptFilter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only type Filter is valid');
        $this->validator->validate(null, $this->constraint);
    }

    /**
     * @covers ::validate
     */
    public function testValidateAuthorPatternWithInvalidEmailShouldFail(): void
    {
        $filter = new Filter();
        $filter->setType(EntityFilterType::AUTHOR);
        $filter->setPattern('foobar');

        $this->validator->validate($filter, $this->constraint);
        $this->buildViolation(IsValidPattern::MESSAGE_EMAIL)->atPath('property.path.pattern')->assertRaised();
    }

    /**
     * @covers ::validate
     */
    public function testValidateAuthorPatternWithValidEmailShouldPass(): void
    {
        $filter = new Filter();
        $filter->setType(EntityFilterType::AUTHOR);
        $filter->setPattern('foo@bar.com');

        $this->validator->validate($filter, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testValidateSubjectPatternWithInvalidRegexShouldFail(): void
    {
        $filter = new Filter();
        $filter->setType(EntityFilterType::SUBJECT);
        $filter->setPattern('foobar');

        $this->validator->validate($filter, $this->constraint);
        $this->buildViolation(IsValidPattern::MESSAGE_REGEX)->atPath('property.path.pattern')->assertRaised();
    }

    /**
     * @covers ::validate
     */
    public function testValidateFilePatternWithInvalidRegexShouldFail(): void
    {
        $filter = new Filter();
        $filter->setType(EntityFilterType::FILE);
        $filter->setPattern('foobar');

        $this->validator->validate($filter, $this->constraint);
        $this->buildViolation(IsValidPattern::MESSAGE_REGEX)->atPath('property.path.pattern')->assertRaised();
    }

    /**
     * @covers ::validate
     */
    public function testValidateSubjectPatternWithValidRegexShouldPass(): void
    {
        $filter = new Filter();
        $filter->setType(EntityFilterType::SUBJECT);
        $filter->setPattern('/^test$/i');

        $this->validator->validate($filter, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testValidateInvalidTypeShouldThrowException(): void
    {
        $filter = new Filter();
        $filter->setType('foobar');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid filter type: foobar');
        $this->validator->validate($filter, $this->constraint);
    }

    protected function createValidator(): IsValidPatternValidator
    {
        return new IsValidPatternValidator();
    }
}
