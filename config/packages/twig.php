<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig, ContainerConfigurator $configurator): void {
    $twig
        ->defaultPath('%kernel.project_dir%/templates')
        ->path('%kernel.project_dir%/public/assets/styles', 'styles')
        ->formThemes(['bootstrap_5_layout.html.twig']);
};
