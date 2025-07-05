<?php
declare(strict_types=1);

use Symfony\Config\NelmioCorsConfig;

return static function (NelmioCorsConfig $config): void {
    $config->defaults()
        ->allowOrigin(['%env(APP_ABSOLUTE_URL)%'])
        ->allowCredentials(true)
        ->allowHeaders(['Content-Type', 'Authorization'])
        ->allowMethods(['GET', 'OPTIONS', 'POST', 'PUT', 'DELETE'])
        ->exposeHeaders(['Link'])
        ->maxAge(3600);
    $config
        ->paths('^/')
        ->originRegex(false);
};
