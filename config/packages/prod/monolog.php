<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

/**
 * Setup summary:
 * - Level: INFO  - /logs/prod.log - !event, !console, !deprecation
 * - Level: ERROR - /logs/prod.error.log - !event, !console, !deprecation  (fingers crossed)
 * - Level: DEBUG - /logs/deprecations.log - deprecation
 * - Level: ERROR - mailer - !console
 * - Level: DEBUG - stderr - !event, !deprecation, !console
 * - Level: DEBUG - console - !event, !deprecation
 */
return App::config([
    'monolog' => [
        'handlers' => [
            'info'                  => [
                'type'                   => 'rotating_file',
                'path'                   => '%kernel.logs_dir%/%kernel.environment%.log',
                'level'                  => 'info',
                'max_files'              => 10,
                'channels'               => ['!event', '!console', '!deprecation'],
                'process_psr_3_messages' => ['remove_used_context_fields' => true],
            ],
            'ai'                    => [
                'type'      => 'rotating_file',
                'path'      => '%kernel.logs_dir%/%kernel.environment%.ai.log',
                'level'     => 'info',
                'max_files' => 10,
                'channels'  => ['ai'],
            ],
            'error_fingers_crossed' => [
                'type'                   => 'fingers_crossed',
                'action_level'           => 'error',
                'handler'                => 'error',
                'channels'               => ['!event', '!console', '!deprecation'],
                'process_psr_3_messages' => ['remove_used_context_fields' => true],
            ],
            'error'                 => [
                'type'      => 'rotating_file',
                'path'      => '%kernel.logs_dir%/%kernel.environment%.error.log',
                'level'     => 'debug',
                'max_files' => 10,
            ],
            'deprecations'          => [
                'type'                   => 'rotating_file',
                'path'                   => '%kernel.logs_dir%/deprecations.%kernel.environment%.log',
                'level'                  => 'debug',
                'max_files'              => 1,
                'channels'               => ['deprecation'],
                'process_psr_3_messages' => ['remove_used_context_fields' => true],
            ],
            // error mailer
            'main'                  => [
                'type'                   => 'fingers_crossed',
                'level'                  => 'error',
                'handler'                => 'deduplicated',
                'channels'               => ['!console'],
                'excluded_http_codes'    => [400, 403, 404, 405],
                'process_psr_3_messages' => ['remove_used_context_fields' => true],
            ],
            'deduplicated'          => [
                'type'    => 'deduplication',
                'handler' => 'symfony_mailer',
            ],
            'symfony_mailer'        => [
                'type'         => 'symfony_mailer',
                'from_email'   => '%env(MAILER_SENDER)%',
                'to_email'     => ['%env(ERROR_MAIL)%'],
                'subject'      => '[123view] %%message%%',
                'level'        => 'error',
                'formatter'    => 'monolog.formatter.html',
                'content_type' => 'text/html',
            ],
            'docker'                => [
                'type'                   => 'error_log',
                'level'                  => 'debug',
                'channels'               => ['!event', '!deprecation'],
                'process_psr_3_messages' => ['remove_used_context_fields' => true,],
            ],
            'console'               => [
                'type'                   => 'console',
                'level'                  => 'debug',
                'process_psr_3_messages' => ['remove_used_context_fields' => true,],
                'channels'               => ['!event', '!deprecation', '!console'],
            ],
        ],
    ],
]);
