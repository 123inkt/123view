<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Repository\Credential;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BasicAuthCredential::class)]
class BasicAuthCredentialTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getAuthorizationHeader', '__toString', 'fromString']);
        static::assertAccessorPairs(BasicAuthCredential::class, $config);
    }

    public function testFromStringWithEmptyString(): void
    {
        $credential = BasicAuthCredential::fromString('');
        static::assertEquals(new BasicAuthCredential(), $credential);
    }

    public function testFromStringInvalidBase64String(): void
    {
        $credential = BasicAuthCredential::fromString('#######');
        static::assertEquals(new BasicAuthCredential(), $credential);
    }

    public function testFromStringInvalidCredentials(): void
    {
        $credential = BasicAuthCredential::fromString(base64_encode('foobar'));
        static::assertEquals(new BasicAuthCredential(), $credential);
    }

    public function testFromString(): void
    {
        $credential = BasicAuthCredential::fromString(base64_encode('sherlock:holmes'));
        static::assertSame('sherlock', $credential->getUsername());
        static::assertSame('holmes', $credential->getPassword());
    }

    public function testToString(): void
    {
        $string     = base64_encode('sherlock:holmes');
        $credential = BasicAuthCredential::fromString($string);
        static::assertSame($string, (string)$credential);
    }

    public function testGetAuthorizationHeader(): void
    {
        $string     = base64_encode('sherlock:holmes');
        $credential = BasicAuthCredential::fromString($string);
        static::assertSame('Basic ' . $string, $credential->getAuthorizationHeader());
    }
}
