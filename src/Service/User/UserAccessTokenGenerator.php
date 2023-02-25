<?php
declare(strict_types=1);

namespace DR\Review\Service\User;

use DR\Review\Repository\User\UserAccessTokenRepository;
use Exception;
use RuntimeException;

class UserAccessTokenGenerator
{
    private const MAX_GENERATION_ATTEMPTS = 10;
    private const IDENTIFIER_LENGTH       = 40;

    public function __construct(private readonly UserAccessTokenRepository $accessTokenRepository)
    {
    }

    /**
     * @throws Exception
     */
    public function generate(): string
    {
        for ($i = 0; $i < self::MAX_GENERATION_ATTEMPTS; $i++) {
            // results in an 80 character length string
            $identifier = bin2hex(random_bytes(self::IDENTIFIER_LENGTH));

            if ($this->accessTokenRepository->findOneBy(['token' => $identifier]) === null) {
                return $identifier;
            }
        }

        throw new RuntimeException('Failed to generate access token');
    }
}
