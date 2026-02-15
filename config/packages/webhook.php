<?php

declare(strict_types=1);

use DR\Review\RemoteEvent\GitlabRemoteEvent;
use DR\Review\Webhook\GitlabRequestParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'webhook' => [
            'enabled' => true,
            'routing' => [
                GitlabRemoteEvent::REMOTE_EVENT_TYPE => [
                    'service' => GitlabRequestParser::class,
                    'secret'  => '%env(GITLAB_WEBHOOK_SECRET)%'
                ],
            ],
        ],
    ],
]);
