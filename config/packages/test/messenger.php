<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();
    $messenger->enabled(false);
    $messenger->transport('async_messages')->dsn('sync://');
    $messenger->transport('async_revisions')->dsn('sync://');
    $messenger->transport('async_delay_mail')->dsn('sync://');
};
