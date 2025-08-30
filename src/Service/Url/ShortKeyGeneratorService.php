<?php
declare(strict_types=1);

namespace DR\Review\Service\Url;

use DR\Review\Repository\Url\ShortUrlRepository;
use RuntimeException;
use Throwable;

readonly class ShortKeyGeneratorService
{
    private const CHARACTERS     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_.+-';
    private const MIN_KEY_LENGTH = 4;

    public function __construct(private ShortUrlRepository $repository, private int $maxAttempts = 50)
    {
    }

    /**
     * Generate a unique short key by progressively increasing length until unique key is found
     * @throws Throwable
     */
    public function generateUniqueShortKey(): string
    {
        $attempts = self::MIN_KEY_LENGTH;

        do {
            $shortKey = $this->generateRandomKey($attempts);
            $attempts++;
        } while ($this->repository->findOneBy(['shortKey' => $shortKey]) !== null && $attempts < $this->maxAttempts);

        if ($attempts >= $this->maxAttempts) {
            throw new RuntimeException('Unable to generate unique short key after maximum attempts');
        }

        return $shortKey;
    }

    /**
     * Generate a random key of specified length using allowed characters
     * @throws Throwable
     */
    public function generateRandomKey(int $length): string
    {
        $charactersLength = strlen(self::CHARACTERS);

        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= self::CHARACTERS[random_int(0, $charactersLength - 1)];
        }

        return $key;
    }
}
