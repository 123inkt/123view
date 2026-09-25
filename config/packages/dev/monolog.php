<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

/**
 * Setup summary:
 * - Level: INFO  - /logs/dev.log - !event, !console, !deprecation
 * - Level: ERROR - /logs/dev.error.log - !event
 * - Level: DEBUG - /logs/doctrine.log - doctrine
 * - Level: DEBUG - /logs/git.log - git
 * - Level: DEBUG - /logs/app.log - app
 * - Level: DEBUG - /logs/deprecations.log - deprecation
 * - Level: DEBUG - stderr - !event, !deprecation, !console
 * - Level: DEBUG - console - !event, !deprecation
 */
return App::config([
    'monolog' => [
        'handlers' => [
            'main' => [
                'type'     => 'rotating_file',
                'path'     => '%kernel.logs_dir%/%kernel.environment%.log',
                'level'    => 'info',
                'max_files' => 1,
                'channels' => ['!event', '!console', '!deprecation'],
                'process_psr_3_messages' => [
                    'remove_used_context_fields' => true,
                ],
            ],
            'ai' => [
                'type'     => 'rotating_file',
                'path'     => '%kernel.logs_dir%/%kernel.environment%.ai.log',
                'level'    => 'info',
                'max_files' => 10,
                'channels' => ['ai'],
            ],
            'error' => [
                'type'                 => 'stream',
                'path'                 => '%kernel.logs_dir%/error.%kernel.environment%.log',
                'level'                => 'error',
                'include_stacktraces' => true,
                'channels'             => ['!event'],
                'process_psr_3_messages' => [
                    'remove_used_context_fields' => true,
                ],
            ],
            'doctrine' => [
                'type'     => 'rotating_file',
                'path'     => '%kernel.logs_dir%/doctrine.%kernel.environment%.log',
                'level'    => 'debug',
                'max_files' => 1,
                'channels' => ['doctrine'],
                'process_psr_3_messages' => [
                    'remove_used_context_fields' => true,
                ],
            ],
            'git' => [
                'type'     => 'rotating_file',
                'path'     => '%kernel.logs_dir%/git.%kernel.environment%.log',
                'level'    => 'debug',
                'max_files' => 1,
                'channels' => ['git'],
                'process_psr_3_messages' => [
                    'remove_used_context_fields' => true,
                ],
            ],
            'app' => [
                'type'     => 'rotating_file',
                'path'     => '%kernel.logs_dir%/app.%kernel.environment%.log',
                'level'    => 'debug',
                'max_files' => 1,
                'channels' => ['app'],
                'process_psr_3_messages' => [
                    'remove_used_context_fields' => true,
                ],
            ],
            'deprecations' => [
                'type'     => 'rotating_file',
                'path'     => '%kernel.logs_dir%/deprecations.%kernel.environment%.log',
                'level'    => 'debug',
                'max_files' => 1,
                'channels' => ['deprecation'],
                'process_psr_3_messages' => [
                    'remove_used_context_fields' => true,
                ],
            ],
            'docker' => [
                'type'     => 'error_log',
                'level'    => 'debug',
                'channels' => ['!event', '!deprecation'],
                'process_psr_3_messages' => [
                    'remove_used_context_fields' => true,
                ],
            ],
            'console' => [
                'type'                   => 'console',
                'level'                  => 'debug',
                'process_psr_3_messages' => false,
                'channels' => ['!event', '!deprecation', '!console'],
            ],
        ],
    ],
]);
