<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;

return static function (ContainerConfigurator $containerConfigurator, FrameworkConfig $framework): void {
    $framework->ide('%env(IDE_URL_PATTERN)%');
};
