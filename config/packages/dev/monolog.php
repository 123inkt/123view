<?php
declare(strict_types=1);

use Symfony\Config\MonologConfig;

return static function (MonologConfig $monolog) {
    $monolog->handler('main')
        ->type('stream')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level('info')
        ->channels()->elements(["!event"]);

    $monolog->handler('app')
        ->type('stream')
        ->path('%kernel.logs_dir%/app.%kernel.environment%.log')
        ->level('debug')
        ->channels()->elements(["app"]);

    $monolog->handler('console')
        ->type('console')
        ->level('debug');
};
