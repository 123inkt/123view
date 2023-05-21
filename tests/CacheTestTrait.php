<?php
declare(strict_types=1);

namespace DR\Review\Tests;

use Closure;
use Symfony\Component\Cache\CacheItem;

trait CacheTestTrait
{
    public static function createCacheItem(string $key, mixed $value, bool $isHit): CacheItem
    {
        return Closure::bind(
            function () use ($key, $value, $isHit): CacheItem {
                $this->key   = $key;
                $this->value = $value;
                $this->isHit = $isHit;

                return $this;
            },
            new CacheItem(),
            CacheItem::class
        )();
    }
}
