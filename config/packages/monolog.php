<?php
declare(strict_types=1);

use Symfony\Config\MonologConfig;

return static function (MonologConfig $monolog): void {
    $monolog->channels(['git', 'deprecation', 'ai']);
};
