<?php
declare(strict_types=1);

use Symfony\Config\MercureConfig;

return static function (MercureConfig $config): void {
    $config->hub('default')
        ->url('%env(MERCURE_URL)%')
        ->publicUrl('%env(MERCURE_PUBLIC_URL)%')
        ->jwt()->secret('%env(MERCURE_JWT_SECRET)%')->publish('*');
};
