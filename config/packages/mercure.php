<?php
declare(strict_types=1);

use Symfony\Config\MercureConfig;

return static function (MercureConfig $config): void {
    $config->hub('default')
        ->url('%env(MERCURE_URL)%')
        ->publicUrl('https://%env(APP_HOSTNAME)%:%env(MERCURE_SSL_PORT)%/.well-known/mercure')
        ->jwt()->secret('%env(MERCURE_JWT_SECRET)%')->publish('*');
};
