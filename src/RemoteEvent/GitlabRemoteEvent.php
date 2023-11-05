<?php
declare(strict_types=1);

namespace DR\Review\RemoteEvent;

use Symfony\Component\RemoteEvent\RemoteEvent;

class GitlabRemoteEvent extends RemoteEvent
{
    /**
     * The event type used in:
     * - the webhook url configuration: /webhook/{type}.
     * - the remote event consumer attribute: #[AsRemoteEventConsumer(type: 'gitlab')].
     * @see /config/packages/webhook.php
     * @see GitlabRemoteEventConsumer
     */
    public const REMOTE_EVENT_TYPE = 'gitlab';
}
