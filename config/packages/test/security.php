<?php

declare(strict_types=1);

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $securityConfig): void {
    $securityConfig->passwordHasher(PasswordAuthenticatedUserInterface::class)
        ->algorithm('auto')
        ->cost(4)
        ->timeCost(3)
        ->memoryCost(10);
};
