<?php

declare(strict_types=1);

use Liip\MonitorBundle\Controller\HealthCheckController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    /* @see vendor/liip/monitor-bundle/Resources/config/routing.xml */
    $routes
        ->add('liip_monitor_run_all_checks', '/health')
        ->methods(['GET'])
        ->controller([HealthCheckController::class, 'runAllChecksAction']);
};
