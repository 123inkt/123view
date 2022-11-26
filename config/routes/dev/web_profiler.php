<?php
declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml')
        ->prefix('/_wdt')
        ->stateless(true);
    $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml')
        ->prefix('/_profiler')
        ->stateless(true);
};
