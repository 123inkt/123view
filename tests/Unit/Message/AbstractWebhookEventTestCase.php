<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Tests\AbstractTestCase;

abstract class AbstractWebhookEventTestCase extends AbstractTestCase
{
    /**
     * @param array<string, int|string|bool|float|null> $payload
     */
    protected static function assertWebhookEvent(CodeReviewAwareInterface $event, string $name, int $reviewId, array $payload): void
    {
        static::assertSame($name, $event->getName());
        static::assertSame($reviewId, $event->getReviewId());
        static::assertSame($payload, $event->getPayload());
    }
}
