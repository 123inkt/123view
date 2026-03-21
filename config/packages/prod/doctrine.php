<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'doctrine' => [
        'orm' => [
            'entity_managers' => [
                'default' => [
                    'metadata_cache_driver' => ['type' => 'pool', 'pool' => 'doctrine.system_cache_pool'],
                    'query_cache_driver'    => ['type' => 'pool', 'pool' => 'doctrine.system_cache_pool'],
                    'result_cache_driver'   => ['type' => 'pool', 'pool' => 'doctrine.result_cache_pool'],
                ],
            ],
        ],
    ],
]);
