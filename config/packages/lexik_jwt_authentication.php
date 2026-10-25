<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'lexik_jwt_authentication' => [
        'secret_key' => '%env(resolve:JWT_SECRET_KEY)%',
        'public_key' => '%env(resolve:JWT_PUBLIC_KEY)%',
        'pass_phrase' => '%env(JWT_PASSPHRASE)%',
        'token_extractors' => [
            'cookie' => ['enabled' => true, 'name' => 'jwtToken'],
        ],
    ]
]);
