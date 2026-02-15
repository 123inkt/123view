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
use Symfony\Component\DependencyInjection\Loader\Configurator\App;
use Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage;

return App::config([
    'framework' => [
        'messenger' => [
            'failure_transport' => 'failed',
            // https://symfony.com/doc/current/messenger.html#transport-configuration
            'transports'        => [
                'async_messages'   => ['dsn' => '%env(MESSENGER_TRANSPORT_DSN)%messages'],
                'async_revisions'  => ['dsn' => '%env(MESSENGER_TRANSPORT_DSN)%revisions'],
                'async_ai_review'  => ['dsn' => '%env(MESSENGER_TRANSPORT_DSN)%ai-review'],
                'async_delay_mail' => ['dsn' => '%env(MESSENGER_TRANSPORT_DSN)%mail'],
                'failed'           => ['dsn' => 'doctrine://default?queue_name=failed'],
                'sync'             => ['dsn' => 'sync://'],
            ],
            // https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport
            'routing'           => [
                // Webhook + RemoteEvent handling
                ConsumeRemoteEventMessage::class       => ['senders' => ['async_messages']],
                FetchRepositoryRevisionsMessage::class => ['senders' => ['async_revisions']],
                CommitAddedMessage::class              => ['senders' => ['async_revisions']],
                CommitRemovedMessage::class            => ['senders' => ['async_revisions']],
                ValidateRevisionsMessage::class        => ['senders' => ['async_revisions']],
                RepositoryUpdatedMessage::class        => ['senders' => ['async_revisions']],
                AiReviewRequested::class               => ['senders' => ['async_ai_review', 'async_messages']],
                DelayableMessage::class                => ['senders' => ['async_delay_mail']],
                AsyncMessageInterface::class           => ['senders' => ['async_messages']],
            ],
        ],
    ],
]);
