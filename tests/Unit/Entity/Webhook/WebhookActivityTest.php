<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Webhook;

use DR\Review\Entity\Webhook\WebhookActivity;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Webhook\WebhookActivity
 */
class WebhookActivityTest extends AbstractTestCase
{
    /**
     * @covers ::setId
     * @covers ::getId
     * @covers ::getRequest
     * @covers ::setRequest
     * @covers ::getRequestHeaders
     * @covers ::setRequestHeaders
     * @covers ::getStatusCode
     * @covers ::setStatusCode
     * @covers ::getResponse
     * @covers ::setResponse
     * @covers ::getResponseHeaders
     * @covers ::setResponseHeaders
     * @covers ::getCreateTimestamp
     * @covers ::setCreateTimestamp
     * @covers ::getWebhook
     * @covers ::setWebhook
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(WebhookActivity::class);
    }
}
