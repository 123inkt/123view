<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Revision;

use DR\GitCommitNotification\Message\Revision\NewRevisionMessage;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractWebhookEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Revision\NewRevisionMessage
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
