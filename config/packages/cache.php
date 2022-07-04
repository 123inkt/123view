<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $cache = $framework->cache();
    $cache->app('cache.adapter.filesystem');
    $cache->directory('%kernel.cache_dir%/pools');
    $cache->pool('upsource.cache')->defaultLifetime(3600);
    $cache->pool('gitlab.cache')->defaultLifetime(3600);
};
