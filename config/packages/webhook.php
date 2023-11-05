<?php

declare(strict_types=1);

use DR\Review\RemoteEvent\GitlabRemoteEvent;
use DR\Review\Webhook\GitlabRequestParser;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $webhook = $framework->webhook();
    $webhook->enabled(true);
    $webhook->routing(GitlabRemoteEvent::REMOTE_EVENT_TYPE)
        ->service(GitlabRequestParser::class)
        ->secret('%env(GITLAB_WEBHOOK_SECRET)%');
};
