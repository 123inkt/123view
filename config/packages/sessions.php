<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->session()
        // Enables session support. Note that the session will ONLY be started if you read or write from it.
        ->enabled(true)
        // ID of the service used for session storage
        // NULL means that Symfony uses PHP default session mechanism
        ->handlerId(null)
        // improves the security of the cookies used for sessions
        ->cookieSecure('auto')
        ->cookieSamesite('lax')
        ->storageFactoryId('session.storage.factory.native');
};
