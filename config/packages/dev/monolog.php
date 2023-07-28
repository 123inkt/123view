<?php
declare(strict_types=1);

use Symfony\Config\MonologConfig;

return static function (MonologConfig $monolog) {
    $monolog->handler('main')
        ->type('stream')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level('info')
        ->channels()->elements(["!event"]);

    $monolog->handler('error')
        ->type('stream')
        ->path('%kernel.logs_dir%/error.%kernel.environment%.log')
        ->level('error')
        ->includeStacktraces(true)
        ->channels()->elements(["!event"]);

    $monolog->handler('doctrine')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/doctrine.%kernel.environment%.log')
        ->level('debug')
        ->maxFiles(1)
        ->channels()->elements(["doctrine"]);

    $monolog->handler('git')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/git.%kernel.environment%.log')
        ->level('debug')
        ->maxFiles(1)
        ->channels()->elements(["git"]);

    $monolog->handler('app')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/app.%kernel.environment%.log')
        ->level('debug')
        ->maxFiles(1)
        ->channels()->elements(["app"]);

    $monolog->handler('console')
        ->type('console')
        ->level('debug');
};
