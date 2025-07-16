<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('lexik_jwt_access_token', '/api/token/acquire');
    $routes->add('gesdinet_jwt_refresh_token', '/api/token/refresh');
};
