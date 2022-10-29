<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\NewRevisionMessage;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\NewRevisionMessage
 */
class NewRevisionMessageTest extends AbstractWebhookEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertWebhookEvent(new NewRevisionMessage(5), 'revision-added', ['revisionId' => 5]);
    }
}
