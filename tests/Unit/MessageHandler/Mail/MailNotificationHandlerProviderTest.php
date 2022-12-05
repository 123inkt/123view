<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Mail;

use ArrayObject;
use DR\Review\MessageHandler\Mail\MailNotificationHandlerInterface;
use DR\Review\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\Mail\MailNotificationHandlerProvider
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
