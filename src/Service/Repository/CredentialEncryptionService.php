<?php
declare(strict_types=1);

namespace DR\Review\Service\Repository;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CredentialEncryptionService
{
    private const string VERSION_PREFIX = 'v1:';

    private string $key;

    public function __construct(#[Autowire(env: 'REPOSITORY_CREDENTIAL_ENCRYPTION_KEY')] string $encodedKey,)
    {
        $decodedKey = base64_decode($encodedKey, strict: true);
        if ($decodedKey === false || strlen($decodedKey) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new RuntimeException(
                sprintf(
                    'REPOSITORY_CREDENTIAL_ENCRYPTION_KEY must be a base64-encoded %d-byte key.',
                    SODIUM_CRYPTO_SECRETBOX_KEYBYTES
                )
            );
        }

        $this->key = $decodedKey;
    }

    public function isEncrypted(string $value): bool
    {
        return str_starts_with($value, self::VERSION_PREFIX);
    }

    public function encrypt(string $plaintext): string
    {
        $nonce      = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->key);

        return self::VERSION_PREFIX . base64_encode($nonce . $ciphertext);
    }

    /**
     * Decrypt a versioned ciphertext. Falls back to returning plaintext as-is for legacy
     * unencrypted values, to support rolling deployment before the migration command is run.
     */
    public function decrypt(string $value): string
    {
        if ($this->isEncrypted($value) === false) {
            return $value;
        }

        $encoded = substr($value, strlen(self::VERSION_PREFIX));
        $decoded = base64_decode($encoded, strict: true);
        if ($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new RuntimeException('Invalid credential ciphertext: malformed base64 or too short.');
        }

        $nonce      = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);
        if ($plaintext === false) {
            throw new RuntimeException('Failed to decrypt credential: authentication tag mismatch.');
        }

        return $plaintext;
    }
}
