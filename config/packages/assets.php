<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'assets' => [
            'enabled'            => true,
            'json_manifest_path' => '%kernel.project_dir%/public/build/manifest.json',
        ],
    ],
]);
