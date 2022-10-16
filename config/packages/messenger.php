<?php
declare(strict_types=1);

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\FetchRepositoryRevisionsMessage;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();
    $messenger->failureTransport('failed');

    // https://symfony.com/doc/current/messenger.html#transport-configuration
    $messenger->transport('async_messages')->dsn('%env(MESSENGER_TRANSPORT_DSN)%messages');
    $messenger->transport('async_revisions')->dsn('%env(MESSENGER_TRANSPORT_DSN)%revisions');
    $messenger->transport('failed')->dsn('doctrine://default?queue_name=failed');
    $messenger->transport('sync')->dsn('sync://');

    // https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport
    $messenger->routing(FetchRepositoryRevisionsMessage::class)->senders(['async_revisions']);
    $messenger->routing(AsyncMessageInterface::class)->senders(['async_messages']);
};
