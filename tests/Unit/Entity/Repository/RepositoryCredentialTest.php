<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Repository;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use DR\Review\Entity\Repository\Credential\CredentialInterface;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RepositoryCredential::class)]
class RepositoryCredentialTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getCredentials', 'setCredentials']);
        static::assertAccessorPairs(RepositoryCredential::class, $config);
    }

    public function testGetSetCredentials(): void
    {
        $credentials          = new BasicAuthCredential('sherlock', 'holmes');
        $repositoryCredential = new RepositoryCredential();
        $repositoryCredential->setCredentials($credentials);

        static::assertSame(AuthenticationType::BASIC_AUTH, $repositoryCredential->getAuthType());
        static::assertSame((string)$credentials, $repositoryCredential->getValue());

        $newCredentials = $repositoryCredential->getCredentials();
        static::assertInstanceOf(BasicAuthCredential::class, $newCredentials);
        static::assertSame('sherlock', $newCredentials->getUsername());
        static::assertSame('holmes', $newCredentials->getPassword());
    }

    public function testSetInvalidCredentials(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown credential type');
        $repositoryCredential = new RepositoryCredential();
        $repositoryCredential->setCredentials(static::createStub(CredentialInterface::class));
    }

    public function testGetInvalidCredentials(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown auth type');
        $repositoryCredential = new RepositoryCredential();
        $repositoryCredential->setAuthType('foobar');
        $repositoryCredential->getCredentials();
    }
}
