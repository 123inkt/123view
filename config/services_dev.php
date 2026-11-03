<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->load('DR\\Review\\Tests\\DataFixtures\\Dev\\', '../tests/DataFixtures/Dev/*')
        ->autowire()
        ->autoconfigure()
        ->tag('doctrine.fixture.orm');
};
