<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'liip_monitor' => [
        'enable_controller' => true,
        'checks'            => [
            'groups' => [
                'default' => [
                    'php_extensions'       => ['amqp', 'intl', 'iconv', 'pdo_mysql', 'json'],
                    'opcache_memory'       => ['warning' => 70, 'critical' => 90],
                    'disk_usage'           => ['warning' => 70, 'critical' => 90, 'path' => '%kernel.cache_dir%'],
                    'apc_memory'           => ['warning' => 70, 'critical' => 90],
                    'apc_fragmentation'    => ['warning' => 70, 'critical' => 90],
                    'messenger_transports' => [
                        'async_messages'   => ['warning_threshold' => 10, 'critical_threshold' => 50],
                        'async_revisions'  => ['warning_threshold' => 10, 'critical_threshold' => 50],
                        'async_delay_mail' => ['warning_threshold' => 10, 'critical_threshold' => 50],
                    ],
                ],
            ],
        ],
    ],
]);
