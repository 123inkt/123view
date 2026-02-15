<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'api_platform' => [
        'title'                       => '%env(APP_NAME)% API',
        'version'                     => '1.0.0',
        'show_webby'                  => true,
        'path_segment_name_generator' => 'api_platform.metadata.path_segment_name_generator.dash',
        'mapping'                     => ['paths' => ['%kernel.project_dir%/src/Entity']],
        'swagger'                     => [
            'versions' => [3],
            'api_keys' => ['Bearer' => ['name' => 'Authorization', 'type' => 'header']],
        ],
        'mercure'                     => ['enabled' => false],
        'messenger'                   => ['enabled' => false],
        'formats'                     => [
            'json' => ['mime_types' => ['application/json']],
            'html' => ['mime_types' => ['text/html']],
        ],
        'patch_formats'               => ['json' => ['mime_types' => ['application/merge-patch+json']]],
        'defaults'                    => [
            // allow custom pagination parameters client side
            'pagination_client_enabled'         => false,
            'pagination_client_items_per_page'  => true,
            // The default number of items per page
            'pagination_items_per_page'         => 30,
            // The default maximum number of items per page
            'pagination_maximum_items_per_page' => 100,
        ],
    ],
]);
