<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'secret'                => '%env(APP_SECRET)%',
        'http_method_override'  => true,
        'php_errors'            => ['log' => true],
        'handle_all_throwables' => true,
        'annotations'           => ['enabled' => false],
        'property_info'         => ['with_constructor_extractor' => true],
    ],
]);
