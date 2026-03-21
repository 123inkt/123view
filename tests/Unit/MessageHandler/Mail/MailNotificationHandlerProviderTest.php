<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Mail;

use ArrayObject;
use DR\Review\MessageHandler\Mail\MailNotificationHandlerInterface;
use DR\Review\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MailNotificationHandlerProvider::class)]
class MailNotificationHandlerProviderTest extends AbstractTestCase
{
    public function testGetHandler(): void
    {
        $handler  = static::createStub(MailNotificationHandlerInterface::class);
        $handlers = new ArrayObject(['handler' => $handler]);

        $provider = new MailNotificationHandlerProvider($handlers);
        static::assertNotNull($provider->getHandler('handler'));
    }
}
