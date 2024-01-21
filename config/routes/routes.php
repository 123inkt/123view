<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('../../src/Controller/', 'attribute');
    $routingConfigurator->import('.', 'fd_symfony_log_viewer')->prefix('/log-viewer');
};
