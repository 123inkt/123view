<?php
declare(strict_types=1);

use Symfony\Config\MonologConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (MonologConfig $monolog) {
    $monolog->handler('info')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level('info')
        ->maxFiles(10);

    $monolog->handler('error')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/%kernel.environment%.error.log')
        ->level('error')
        ->maxFiles(10);

    if ((string)env('ERROR_MAIL') !== '') {
        // error mailer
        $monolog->handler('main')
            ->type('fingers_crossed')
            ->level('error')
            ->handler('deduplicated');
        $monolog->handler('deduplicated')
            ->type('deduplication')
            ->handler('symfony_mailer');
        $monolog->handler('symfony_mailer')
            ->type('symfony_mailer')
            ->fromEmail('%env(MAILER_SENDER)%')
            ->toEmail(['%env(ERROR_MAIL)%'])
            ->subject('[Git commit notification] %%message%%')
            ->level('error')
            ->formatter('monolog.formatter.html')
            ->contentType('text/html');
    }

    $monolog->handler('console')
        ->type('console')
        ->level('debug');
};
