<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'messenger' => [
            'transports' => [
                'async_messages'   => ['dsn' => 'sync://'],
                'async_revisions'  => ['dsn' => 'sync://'],
                'async_delay_mail' => ['dsn' => 'sync://'],
            ],
        ],
    ],
]);
