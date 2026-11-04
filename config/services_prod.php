<?php
declare(strict_types=1);

use DR\Review\Monolog\ToolExecutionExceptionActivationStrategy;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()->set(ToolExecutionExceptionActivationStrategy::class)
        ->decorate('monolog.handler.main.http_code_strategy')
        ->args([service('.inner')]);
};
