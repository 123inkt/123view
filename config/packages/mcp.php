<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'mcp' => [
        'servers' => [
            'default' => [
                'name'         => '123view',
                'version'      => '1.0.0',
                'description'  => 'A code review and commit notification application',
                'transports'   => ['http' => true],
                'instructions' => 'This server provides access to code reviews',
                'http'         => [
                    'path'          => '/_mcp',
                    'allowed_hosts' => false,
                ],
                'registry'      => ['*'],
            ],
        ],
    ],
]);
