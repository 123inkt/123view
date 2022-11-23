<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $configurator): void {
    // default configuration for services in *this* file
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('DR\\GitCommitNotification\\Tests\\', '../../../tests/DataFixtures/*')
        ->tag('doctrine.fixture.orm');
};
