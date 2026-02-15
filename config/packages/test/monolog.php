<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'monolog' => [
        'handlers' => [
            'main'   => [
                'type'         => 'fingers_crossed',
                'action_level' => 'error',
                'handler'      => 'nested',
                'channels'     => ['!event'],
            ],
            'nested' => [
                'type'  => 'stream',
                'path'  => '%kernel.logs_dir%/%kernel.environment%.log',
                'level' => 'debug',
            ],
        ],
    ],
]);
