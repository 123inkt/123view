<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler\Mail;

use ArrayObject;
use DR\GitCommitNotification\MessageHandler\Mail\MailNotificationHandlerInterface;
use DR\GitCommitNotification\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\Mail\MailNotificationHandlerProvider
 * @covers ::__construct
 */
class MailNotificationHandlerProviderTest extends AbstractTestCase
{
    /**
     * @covers ::getHandler
     */
    public function testGetHandler(): void
    {
        $handler  = $this->createMock(MailNotificationHandlerInterface::class);
        $handlers = new ArrayObject(['handler' => $handler]);

        $provider = new MailNotificationHandlerProvider($handlers);
        static::assertNotNull($provider->getHandler('handler'));
    }
}
