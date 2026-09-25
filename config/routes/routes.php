<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('../../src/Controller/', 'attribute');
    $routingConfigurator->import('@FrameworkBundle/Resources/config/routing/webhook.php')->prefix('/webhook');
};
