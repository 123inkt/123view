<?php
declare(strict_types=1);

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\Delay\DelayableMessage;
use DR\GitCommitNotification\Message\Revision\FetchRepositoryRevisionsMessage;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    // https://symfony.com/doc/current/messenger.html#transport-configuration
    $messenger->transport('async_messages')->dsn('sync://');
    $messenger->transport('async_revisions')->dsn('sync://');
    $messenger->transport('async_delay_mail')->dsn('sync://');
    $messenger->transport('sync')->dsn('sync://');

    // https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport
    $messenger->routing(FetchRepositoryRevisionsMessage::class)->senders(['async_revisions']);
    $messenger->routing(DelayableMessage::class)->senders(['async_delay_mail']);
    $messenger->routing(AsyncMessageInterface::class)->senders(['async_messages']);
};
