<?php
declare(strict_types=1);

use Symfony\Config\MonologConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

/**
 * Setup summary:
 * - Level: INFO  - /logs/prod.log - !event, !console, !deprecation
 * - Level: ERROR - /logs/prod.error.log - !event, !console, !deprecation  (fingers crossed)
 * - Level: DEBUG - /logs/deprecations.log - deprecation
 * - Level: ERROR - mailer - !console
 * - Level: DEBUG - stderr - !event, !deprecation, !console
 * - Level: DEBUG - console - !event, !deprecation
 */
return static function (MonologConfig $monolog) {
    $monolog->handler('info')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level('info')
        ->formatter('monolog.formatter.line')
        ->maxFiles(10)
        ->channels()->elements(["!event", "!console", "!deprecation"]);

    $monolog->handler('error_fingers_crossed')
        ->type('fingers_crossed')
        ->actionLevel('error')
        ->handler('error')
        ->channels()->elements(["!event", "!console", "!deprecation"]);
    $monolog->handler('error')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/%kernel.environment%.error.log')
        ->level('debug')
        ->formatter('monolog.formatter.line')
        ->maxFiles(10);

    $monolog->handler('deprecations')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/deprecations.%kernel.environment%.log')
        ->level('debug')
        ->formatter('monolog.formatter.line')
        ->maxFiles(1)
        ->channels()->elements(["deprecation"]);

    if ((string)env('ERROR_MAIL') !== '') {
        // error mailer
        $monolog->handler('main')
            ->type('fingers_crossed')
            ->level('error')
            ->handler('deduplicated')
            ->channels()->elements(['!console']);
        $monolog->handler('deduplicated')
            ->type('deduplication')
            ->handler('symfony_mailer');
        $monolog->handler('symfony_mailer')
            ->type('symfony_mailer')
            ->fromEmail('%env(MAILER_SENDER)%')
            ->toEmail(['%env(ERROR_MAIL)%'])
            ->subject('[123view] %%message%%')
            ->level('error')
            ->formatter('monolog.formatter.html')
            ->contentType('text/html');

        $monolog->handler('main')->excludedHttpCode()->code(403)->code(404);
    }

    $monolog->handler('docker')
        ->type('stream')
        ->level('info')
        ->path('php://stderr')
        ->formatter('monolog.formatter.line')
        ->channels()->elements(["!event", "!deprecation", "!console"]);

    $monolog->handler('console')
        ->type('console')
        ->level('debug')
        ->formatter('monolog.formatter.line')
        ->channels()->elements(["!event", "!deprecation"]);
};
