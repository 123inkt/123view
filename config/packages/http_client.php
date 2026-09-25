<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return App::config([
    'framework' => [
        'http_client' => [
            'default_options' => [
                'verify_host' => env('HTTP_CLIENT_VERIFY_HOST')->bool(),
                'verify_peer' => env('HTTP_CLIENT_VERIFY_PEER')->bool(),
            ],
            'scoped_clients'  => [
                'gitlab.client'      => [
                    'base_uri' => '%env(GITLAB_API_URL)%api/v4/',
                    'headers'  => ['PRIVATE-TOKEN' => '%env(GITLAB_ACCESS_TOKEN)%',],
                    'scope'    => 'gitlab',
                ],
                'highlightjs.client' => [
                    'base_uri' => 'http://%env(HIGHLIGHTJS_HOST)%:%env(HIGHLIGHTJS_PORT)%/',
                    'scope'    => 'highlightjs',
                ],
            ],
        ],
    ],
]);
