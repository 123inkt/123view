<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'session' => [
            // Enables session support. Note that the session will ONLY be started if you read or write from it.
            'enabled'               => true,
            // ID of the service used for session storage
            // NULL means that Symfony uses PHP default session mechanism
            'handler_id'            => null,
            // improves the security of the cookies used for sessions
            'cookie_secure'         => 'auto',
            'cookie_samesite'       => 'lax',
            'storage_factory_id'   => 'session.storage.factory.native',
        ],
    ],
]);
