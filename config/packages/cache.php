<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'cache' => [
            'app'       => 'cache.app.file',
            'directory' => '%kernel.project_dir%/var/cache/pools',
            'pools'     => [
                // application caches
                'cache.app.file' => [
                    'adapters'        => ['cache.adapter.filesystem'],
                    'default_lifetime' => '%env(CACHE_APP_LIFETIME)%',
                ],
                'gitlab.cache' => [
                    'default_lifetime' => '%env(CACHE_GITLAB_LIFETIME)%',
                ],
                'revision.cache' => [
                    'default_lifetime' => '%env(CACHE_REVISION_LIFETIME)%', // 1 month
                ],
                // doctrine cache
                'doctrine.result_cache_pool' => [
                    'adapters' => ['cache.app'],
                ],
                'doctrine.system_cache_pool' => [
                    'adapters' => ['cache.system'],
                ],
            ],
        ],
    ],
]);
