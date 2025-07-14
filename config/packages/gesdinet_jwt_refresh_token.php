<?php

declare(strict_types=1);

use DR\Review\Entity\User\JwtRefreshToken;
use Symfony\Config\GesdinetJwtRefreshTokenConfig;

return static function (GesdinetJwtRefreshTokenConfig $config): void {
    $config->refreshTokenClass(JwtRefreshToken::class)
        ->ttlUpdate(true)
        ->tokenParameterName('refreshToken')
        ->returnExpiration(true)
        ->returnExpirationParameterName('refreshTokenExpiresAt')
        ->singleUse(false);
};
