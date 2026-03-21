<?php

declare(strict_types=1);

use DR\Review\Entity\User\JwtRefreshToken;
use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'gesdinet_jwt_refresh_token' => [
        'refresh_token_class'              => JwtRefreshToken::class,
        'ttl_update'                       => true,
        'token_parameter_name'             => 'refreshToken',
        'return_expiration'                => true,
        'return_expiration_parameter_name' => 'refreshTokenExpiresAt',
        'single_use'                       => false,
    ]
]);
