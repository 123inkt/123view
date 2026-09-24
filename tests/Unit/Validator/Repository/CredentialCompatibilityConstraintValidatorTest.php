<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Validator\Repository;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Validator\Repository\CredentialCompatibilityConstraint;
use DR\Review\Validator\Repository\CredentialCompatibilityConstraintValidator;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<CredentialCompatibilityConstraintValidator>
 */
#[CoversClass(CredentialCompatibilityConstraintValidator::class)]
class CredentialCompatibilityConstraintValidatorTest extends ConstraintValidatorTestCase
{
    public function testNonRepositoryValueIsIgnored(): void
    {
        $this->validator->validate(null, new CredentialCompatibilityConstraint());
        $this->assertNoViolation();
    }

    public function testRepositoryWithoutUrlIsIgnored(): void
    {
        $this->validator->validate(new Repository(), new CredentialCompatibilityConstraint());
        $this->assertNoViolation();
    }

    public function testSshUrlWithSshKeyCredentialPasses(): void
    {
        $credential = $this->buildCredential(AuthenticationType::SSH_KEY);

        $repository = (new Repository())->setUrl(Uri::new('ssh://git@github.com/org/repo.git'))->setCredential($credential);

        $this->validator->validate($repository, new CredentialCompatibilityConstraint());
        $this->assertNoViolation();
    }

    public function testSshUrlWithNoCredentialFails(): void
    {
        $constraint = new CredentialCompatibilityConstraint();
        $repository = (new Repository())->setUrl(Uri::new('ssh://git@github.com/org/repo.git'));

        $this->validator->validate($repository, $constraint);
        $this->buildViolation($constraint->messageSshRequiresSshKey)
            ->atPath('property.path.credential')
            ->assertRaised();
    }

    public function testSshUrlWithBasicAuthCredentialFails(): void
    {
        $constraint = new CredentialCompatibilityConstraint();
        $credential = $this->buildCredential(AuthenticationType::BASIC_AUTH);
        $repository = (new Repository())->setUrl(Uri::new('ssh://git@github.com/org/repo.git'))->setCredential($credential);

        $this->validator->validate($repository, $constraint);
        $this->buildViolation($constraint->messageSshRequiresSshKey)
            ->atPath('property.path.credential')
            ->assertRaised();
    }

    public function testHttpsUrlWithNoCredentialPasses(): void
    {
        $repository = (new Repository())->setUrl(Uri::new('https://github.com/org/repo.git'));

        $this->validator->validate($repository, new CredentialCompatibilityConstraint());
        $this->assertNoViolation();
    }

    public function testHttpsUrlWithBasicAuthCredentialPasses(): void
    {
        $credential = $this->buildCredential(AuthenticationType::BASIC_AUTH);
        $repository = (new Repository())->setUrl(Uri::new('https://github.com/org/repo.git'))->setCredential($credential);

        $this->validator->validate($repository, new CredentialCompatibilityConstraint());
        $this->assertNoViolation();
    }

    public function testHttpsUrlWithSshKeyCredentialFails(): void
    {
        $constraint = new CredentialCompatibilityConstraint();
        $credential = $this->buildCredential(AuthenticationType::SSH_KEY);
        $repository = (new Repository())->setUrl(Uri::new('https://github.com/org/repo.git'))->setCredential($credential);

        $this->validator->validate($repository, $constraint);
        $this->buildViolation($constraint->messageHttpForbidsSshKey)
            ->atPath('property.path.credential')
            ->assertRaised();
    }

    protected function createValidator(): CredentialCompatibilityConstraintValidator
    {
        return new CredentialCompatibilityConstraintValidator();
    }

    private function buildCredential(string $authType): RepositoryCredential
    {
        return (new RepositoryCredential())->setAuthType($authType)->setValue('placeholder');
    }
}
