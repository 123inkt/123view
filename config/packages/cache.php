<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $cache = $framework->cache();
    $cache->app('cache.app.file');
    $cache->directory('%kernel.project_dir%/var/cache/pools');

    // application caches
    $cache->pool('cache.app.file')->adapters(['cache.adapter.filesystem'])->defaultLifetime('%env(CACHE_APP_LIFETIME)%');
    $cache->pool('gitlab.cache')->defaultLifetime('%env(CACHE_GITLAB_LIFETIME)%');
    $cache->pool('revision.cache')->defaultLifetime('%env(CACHE_REVISION_LIFETIME)%'); // 1 month

    // doctrine cache
    $cache->pool('doctrine.result_cache_pool')->adapters(['cache.app']);
    $cache->pool('doctrine.system_cache_pool')->adapters(['cache.system']);
};
