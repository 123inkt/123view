<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'nelmio_cors' => [
        'paths'    => ['^/' => ['origin_regex' => false]],
        'defaults' => [
            'allow_origin'      => ['%env(APP_ABSOLUTE_URL)%'],
            'allow_credentials' => true,
            'allow_methods'     => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allow_headers'     => ['Content-Type', 'Authorization'],
            'expose_headers'    => ['Link'],
            'max_age'           => 3600,
        ]
    ]
]);
