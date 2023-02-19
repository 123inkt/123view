<?php
declare(strict_types=1);

namespace DR\Review\Service\User;

use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use Exception;

class UserAccessTokenIssuer
{
    public function __construct(
        private readonly UserAccessTokenGenerator $generator,
        private readonly UserAccessTokenRepository $accessTokenRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function issue(User $user, string $name): void
    {
        $token = (new UserAccessToken())
            ->setIdentifier($this->generator->generate())
            ->setName($name)
            ->setUser($user)
            ->setCreateTimestamp(time())
            ->setUseTimestamp(time());

        $this->accessTokenRepository->save($token, true);
    }
}
