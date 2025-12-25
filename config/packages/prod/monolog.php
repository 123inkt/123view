<?php
declare(strict_types=1);

use Symfony\Config\MonologConfig;

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
        ->maxFiles(10)
        ->channels()->elements(["!event", "!console", "!deprecation"]);

    $monolog->handler('ai')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/%kernel.environment%.ai.log')
        ->level('info')
        ->maxFiles(10)
        ->channels()->elements(["ai"]);

    $monolog->handler('error_fingers_crossed')
        ->type('fingers_crossed')
        ->actionLevel('error')
        ->handler('error')
        ->channels()->elements(["!event", "!console", "!deprecation"]);
    $monolog->handler('error')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/%kernel.environment%.error.log')
        ->level('debug')
        ->maxFiles(10);

    $monolog->handler('deprecations')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/deprecations.%kernel.environment%.log')
        ->level('debug')
        ->maxFiles(1)
        ->channels()->elements(["deprecation"]);

    // error mailer
    $monolog->handler('main')
        ->type('fingers_crossed')
        ->level('error')
        ->handler('deduplicated')
        ->channels()->elements(['!console']);
    $monolog->handler('main')->excludedHttpCode(400);
    $monolog->handler('main')->excludedHttpCode(403);
    $monolog->handler('main')->excludedHttpCode(404);
    $monolog->handler('main')->excludedHttpCode(405);
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

    $monolog->handler('docker')
        ->type('error_log')
        ->level('debug')
        ->channels()->elements(["!event", "!deprecation"]);

    $monolog->handler('console')
        ->type('console')
        ->level('debug')
        ->processPsr3Messages(false)
        ->channels()->elements(["!event", "!deprecation", "!console"]);

    $monolog->handler('main')->processPsr3Messages()->removeUsedContextFields(true);
    $monolog->handler('info')->processPsr3Messages()->removeUsedContextFields(true);
    $monolog->handler('error_fingers_crossed')->processPsr3Messages()->removeUsedContextFields(true);
    $monolog->handler('deprecations')->processPsr3Messages()->removeUsedContextFields(true);
    $monolog->handler('docker')->processPsr3Messages()->removeUsedContextFields(true);
    $monolog->handler('console')->processPsr3Messages()->removeUsedContextFields(true);
};
