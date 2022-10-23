<?php

declare(strict_types=1);

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrineConfig): void {
    $doctrineConfig->orm()->autoGenerateProxyClasses(true);

    $em = $doctrineConfig->orm()->entityManager('default');
    $em->metadataCacheDriver()->type('pool')->pool('doctrine.system_cache_pool');
    $em->queryCacheDriver()->type('pool')->pool('doctrine.system_cache_pool');
    $em->resultCacheDriver()->type('pool')->pool('doctrine.result_cache_pool');
};
