<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Webhook;

use DR\Review\Entity\Webhook\WebhookActivity;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookActivity::class)]
class WebhookActivityTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(WebhookActivity::class);
    }
}
