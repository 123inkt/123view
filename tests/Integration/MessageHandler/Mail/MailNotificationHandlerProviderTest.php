<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\MessageHandler\Mail;

use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\MessageHandler\Mail\CommentAddedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentReplyUpdatedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentResolvedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentUpdatedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\Review\Tests\AbstractKernelTestCase;
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
