<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Repository;

use DR\Review\Service\Repository\CredentialEncryptionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(CredentialEncryptionService::class)]
class CredentialEncryptionServiceTest extends AbstractTestCase
{
    private string $validKey;
    private CredentialEncryptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // 32-byte all-zero key, base64-encoded
        $this->validKey = base64_encode(str_repeat("\x00", SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
        $this->service  = new CredentialEncryptionService($this->validKey);
    }

    public function testConstructorAcceptsValidKey(): void
    {
        // No exception thrown – constructor succeeds for a valid 32-byte key
        $service = new CredentialEncryptionService($this->validKey);
        self::assertFalse($service->isEncrypted('plaintext'));
    }

    public function testConstructorRejectsInvalidBase64(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REPOSITORY_CREDENTIAL_ENCRYPTION_KEY must be a base64-encoded');

        new CredentialEncryptionService('not-valid-base64!!!');
    }

    public function testConstructorRejectsWrongKeyLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REPOSITORY_CREDENTIAL_ENCRYPTION_KEY must be a base64-encoded');

        // 16 bytes instead of 32
        new CredentialEncryptionService(base64_encode(str_repeat("\x00", 16)));
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        $plaintext  = 'username:secret-password';
        $ciphertext = $this->service->encrypt($plaintext);

        self::assertStringStartsWith('v1:', $ciphertext);
        self::assertSame($plaintext, $this->service->decrypt($ciphertext));
    }

    public function testEncryptProducesRandomNonces(): void
    {
        $plaintext   = 'same plaintext';
        $ciphertext1 = $this->service->encrypt($plaintext);
        $ciphertext2 = $this->service->encrypt($plaintext);

        self::assertNotSame($ciphertext1, $ciphertext2);
    }

    public function testIsEncryptedReturnsTrueForVersionedValue(): void
    {
        $encrypted = $this->service->encrypt('secret');
        self::assertTrue($this->service->isEncrypted($encrypted));
    }

    public function testIsEncryptedReturnsFalseForPlaintext(): void
    {
        self::assertFalse($this->service->isEncrypted('username:password'));
    }

    public function testDecryptLegacyPlaintextPassThrough(): void
    {
        $plaintext = 'username:password';
        self::assertSame($plaintext, $this->service->decrypt($plaintext));
    }

    public function testDecryptThrowsOnTamperedCiphertext(): void
    {
        $ciphertext = $this->service->encrypt('secret');
        // Flip the last base64 character to corrupt the MAC
        $tampered = substr($ciphertext, 0, -2) . 'AA';

        $this->expectException(RuntimeException::class);
        $this->service->decrypt($tampered);
    }

    public function testDecryptThrowsOnMalformedBase64(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid credential ciphertext');

        $this->service->decrypt('v1:!!!not-base64!!!');
    }
}
