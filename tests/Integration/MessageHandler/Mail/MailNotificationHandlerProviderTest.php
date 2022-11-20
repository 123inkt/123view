<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Integration\MessageHandler\Mail;

use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyUpdated;
use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Message\Comment\CommentUpdated;
use DR\GitCommitNotification\MessageHandler\Mail\CommentAddedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\CommentReplyUpdatedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\CommentResolvedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\CommentUpdatedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\GitCommitNotification\Tests\AbstractKernelTestCase;
use Exception;

/**
 * @coversNothing
 */
class MailNotificationHandlerProviderTest extends AbstractKernelTestCase
{
    /**
     * @throws Exception
     */
    public function testGetHandler(): void
    {
        /** @var MailNotificationHandlerProvider $provider */
        $provider = self::getContainer()->get(MailNotificationHandlerProvider::class);

        static::assertInstanceOf(CommentAddedMailNotificationHandler::class, $provider->getHandler(CommentAdded::class));
        static::assertInstanceOf(CommentUpdatedMailNotificationHandler::class, $provider->getHandler(CommentUpdated::class));
        static::assertInstanceOf(CommentResolvedMailNotificationHandler::class, $provider->getHandler(CommentResolved::class));
        static::assertInstanceOf(CommentReplyAddedMailNotificationHandler::class, $provider->getHandler(CommentReplyAdded::class));
        static::assertInstanceOf(CommentReplyUpdatedMailNotificationHandler::class, $provider->getHandler(CommentReplyUpdated::class));
        static::assertNull($provider->getHandler('foobar'));
    }
}
