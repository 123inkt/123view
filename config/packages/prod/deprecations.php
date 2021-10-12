<?php
declare(strict_types=1);

use Symfony\Config\MonologConfig;

return static function (MonologConfig $monolog) {
    $monolog->channels(['deprecation']);
    $monolog->handler('deprecation')
        ->type('stream')
        ->path('php://stderr')
        ->channel('deprecation');
};
