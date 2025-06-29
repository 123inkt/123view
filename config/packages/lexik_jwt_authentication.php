<?php

declare(strict_types=1);

use Symfony\Config\LexikJwtAuthenticationConfig;

return static function (LexikJwtAuthenticationConfig $config): void {
    $config
        ->secretKey('%env(resolve:JWT_SECRET_KEY)%')
        ->publicKey('%env(resolve:JWT_PUBLIC_KEY)%')
        ->passPhrase('%env(JWT_PASSPHRASE)%');
};
