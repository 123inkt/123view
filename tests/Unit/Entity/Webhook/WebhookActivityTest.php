<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Webhook;

use DR\GitCommitNotification\Entity\Webhook\WebhookActivity;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Webhook\WebhookActivity
 */
class WebhookActivityTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(WebhookActivity::class);
    }
}
