<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'router' => [
            'enabled'             => true,
            'default_uri'         => '%env(APP_ABSOLUTE_URL)%',
            'strict_requirements' => true,
            'utf8'                => true,
        ],
    ],
]);
