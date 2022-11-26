<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $cache = $framework->cache();
    $cache->app('cache.adapter.filesystem');
    $cache->directory('%kernel.cache_dir%/pools');

    // application caches
    $cache->pool('upsource.cache')->defaultLifetime(3600);
    $cache->pool('gitlab.cache')->defaultLifetime(3600);
    $cache->pool('revision.cache')->defaultLifetime(2628000); // 1 month

    // doctrine cache
    $cache->pool('doctrine.result_cache_pool')->adapters(['cache.app']);
    $cache->pool('doctrine.system_cache_pool')->adapters(['cache.system']);
};
