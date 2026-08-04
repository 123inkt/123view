<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Repository\Credential;

use DR\Review\Entity\Repository\Credential\SshKeyCredential;
use DR\Review\Tests\AbstractTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SshKeyCredential::class)]
class SshKeyCredentialTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(SshKeyCredential::class);
    }

    public function testFromStringWithEmptyString(): void
    {
        $credential = SshKeyCredential::fromString('');
        static::assertNull($credential->getPrivateKey());
    }

    public function testFromString(): void
    {
        $key        = "-----BEGIN OPENSSH PRIVATE KEY-----\nfoobar\n-----END OPENSSH PRIVATE KEY-----";
        $credential = SshKeyCredential::fromString($key);
        static::assertSame($key, $credential->getPrivateKey());
    }

    public function testToString(): void
    {
        $key        = "-----BEGIN OPENSSH PRIVATE KEY-----\nfoobar\n-----END OPENSSH PRIVATE KEY-----";
        $credential = new SshKeyCredential($key);
        static::assertSame($key, (string)$credential);
    }

    public function testToStringWithNull(): void
    {
        $credential = new SshKeyCredential();
        static::assertSame('', (string)$credential);
    }

    public function testGetAuthorizationHeaderThrows(): void
    {
        $this->expectException(LogicException::class);
        $credential = new SshKeyCredential('key');
        $credential->getAuthorizationHeader();
    }
}
