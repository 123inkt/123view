<?php
declare(strict_types=1);

use Symfony\Config\LiipMonitorConfig;

return static function (LiipMonitorConfig $config): void {
    $config->enableController(true);

    $group = $config->checks()->groups('default');
    $group->phpExtensions(['amqp', 'intl', 'iconv', 'pdo_mysql', 'json']);
    $group->opcacheMemory()->warning(70)->critical(90);
    $group->diskUsage()->warning(70)->critical(90)->path('%kernel.cache_dir%');
    $group->apcMemory()->warning(70)->critical(90);
    $group->apcFragmentation()->warning(70)->critical(90);
    $group->messengerTransports('async_messages')->warningThreshold(10)->criticalThreshold(50);
    $group->messengerTransports('async_revisions')->warningThreshold(10)->criticalThreshold(50);
    $group->messengerTransports('async_delay_mail')->warningThreshold(10)->criticalThreshold(50);
};
