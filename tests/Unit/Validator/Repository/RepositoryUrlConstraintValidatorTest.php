<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Validator\Repository;

use DR\Review\Validator\Repository\RepositoryUrlConstraint;
use DR\Review\Validator\Repository\RepositoryUrlConstraintValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<RepositoryUrlConstraintValidator>
 */
#[CoversClass(RepositoryUrlConstraintValidator::class)]
class RepositoryUrlConstraintValidatorTest extends ConstraintValidatorTestCase
{
    public function testNullValuePassesValidation(): void
    {
        $this->validator->validate(null, new RepositoryUrlConstraint());
        $this->assertNoViolation();
    }

    public function testEmptyStringPassesValidation(): void
    {
        $this->validator->validate('', new RepositoryUrlConstraint());
        $this->assertNoViolation();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validUrlProvider(): iterable
    {
        yield 'https'         => ['https://github.com/org/repo.git'];
        yield 'http'          => ['http://example.com/repo.git'];
        yield 'ssh canonical' => ['ssh://git@github.com/org/repo.git'];
        yield 'scp style'     => ['git@github.com:org/repo.git'];
        yield 'https with port' => ['https://gitlab.example.com:8443/org/repo.git'];
        yield 'ssh with port'   => ['ssh://git@gitlab.example.com:22/org/repo.git'];
    }

    #[DataProvider('validUrlProvider')]
    public function testValidUrlPassesValidation(string $url): void
    {
        $this->validator->validate($url, new RepositoryUrlConstraint());
        $this->assertNoViolation();
    }

    public function testUnsupportedSchemeFails(): void
    {
        $constraint = new RepositoryUrlConstraint();
        $this->validator->validate('ftp://example.com/repo.git', $constraint);
        $this->buildViolation($constraint->messageUnsupportedScheme)->assertRaised();
    }

    public function testSshWithoutUsernameFails(): void
    {
        $constraint = new RepositoryUrlConstraint();
        $this->validator->validate('ssh://github.com/org/repo.git', $constraint);
        $this->buildViolation($constraint->messageSshRequiresUser)->assertRaised();
    }

    protected function createValidator(): RepositoryUrlConstraintValidator
    {
        return new RepositoryUrlConstraintValidator();
    }
}
