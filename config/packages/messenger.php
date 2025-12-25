<?php
declare(strict_types=1);

use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\Delay\DelayableMessage;
use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\Message\Revision\CommitAddedMessage;
use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Message\Revision\RepositoryUpdatedMessage;
use DR\Review\Message\Revision\ValidateRevisionsMessage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();
    $messenger->failureTransport('failed');

    // https://symfony.com/doc/current/messenger.html#transport-configuration
    $messenger->transport('async_messages')->dsn('%env(MESSENGER_TRANSPORT_DSN)%messages');
    $messenger->transport('async_revisions')->dsn('%env(MESSENGER_TRANSPORT_DSN)%revisions');
    $messenger->transport('async_ai_review')->dsn('%env(MESSENGER_TRANSPORT_DSN)%ai-review');
    $messenger->transport('async_delay_mail')->dsn('%env(MESSENGER_TRANSPORT_DSN)%mail');
    $messenger->transport('failed')->dsn('doctrine://default?queue_name=failed');
    $messenger->transport('sync')->dsn('sync://');

    // Webhook + RemoteEvent handling
    $messenger->routing(ConsumeRemoteEventMessage::class)->senders(['async_messages']);

    // https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport
    $messenger->routing(FetchRepositoryRevisionsMessage::class)->senders(['async_revisions']);
    $messenger->routing(CommitAddedMessage::class)->senders(['async_revisions']);
    $messenger->routing(CommitRemovedMessage::class)->senders(['async_revisions']);
    $messenger->routing(ValidateRevisionsMessage::class)->senders(['async_revisions']);
    $messenger->routing(RepositoryUpdatedMessage::class)->senders(['async_revisions']);
    $messenger->routing(AiReviewRequested::class)->senders(['async_ai_review', 'async_messages']);
    $messenger->routing(DelayableMessage::class)->senders(['async_delay_mail']);
    $messenger->routing(AsyncMessageInterface::class)->senders(['async_messages']);
};
