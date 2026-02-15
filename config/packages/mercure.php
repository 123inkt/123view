<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'mercure' => [
        'hubs' => [
            'default' => [
                'url'        => '%env(MERCURE_URL)%',
                'public_url' => 'https://%env(APP_HOSTNAME)%:%env(MERCURE_SSL_PORT)%/.well-known/mercure',
                'jwt'        => ['secret' => '%env(MERCURE_JWT_SECRET)%', 'publish' => '*'],
            ],
        ],
    ],
]);
