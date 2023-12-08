<?php
declare(strict_types=1);

use Symfony\Config\MonologConfig;

return static function (MonologConfig $monolog) {
    $monolog->handler('main')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level('info')
        ->maxFiles(1)
        ->formatter('monolog.formatter.line')
        ->channels()->elements(["!event"]);

    $monolog->handler('error')
        ->type('stream')
        ->path('%kernel.logs_dir%/error.%kernel.environment%.log')
        ->level('error')
        ->formatter('monolog.formatter.line')
        ->includeStacktraces(true)
        ->channels()->elements(["!event"]);

    $monolog->handler('doctrine')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/doctrine.%kernel.environment%.log')
        ->level('debug')
        ->formatter('monolog.formatter.line')
        ->maxFiles(1)
        ->channels()->elements(["doctrine"]);

    $monolog->handler('git')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/git.%kernel.environment%.log')
        ->level('debug')
        ->formatter('monolog.formatter.line')
        ->maxFiles(1)
        ->channels()->elements(["git"]);

    $monolog->handler('app')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/app.%kernel.environment%.log')
        ->level('debug')
        ->formatter('monolog.formatter.line')
        ->maxFiles(1)
        ->channels()->elements(["app"]);

    $monolog->handler('deprecations')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/deprecations.%kernel.environment%.log')
        ->level('debug')
        ->formatter('monolog.formatter.line')
        ->maxFiles(1)
        ->channels()->elements(["deprecation"]);

    $monolog->handler('docker')
        ->type('stream')
        ->level('debug')
        ->path('php://stderr')
        ->formatter('monolog.formatter.line')
        ->channels()->elements(["!event"]);

    $monolog->handler('console')
        ->type('console')
        ->level('debug')
        ->formatter('monolog.formatter.line');
};
