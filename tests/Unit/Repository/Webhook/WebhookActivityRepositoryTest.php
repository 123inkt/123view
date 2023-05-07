<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Webhook;

use DR\Review\Repository\Webhook\WebhookActivityRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\WebhookActivityFixtures;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookActivityRepository::class)]
class WebhookActivityRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testCleanUp(): void
    {
        $activityRepository = self::getService(WebhookActivityRepository::class);

        static::assertSame(0, $activityRepository->cleanUp(123456788));
        static::assertSame(1, $activityRepository->cleanUp(123456790));
        static::assertSame(0, $activityRepository->cleanUp(123456790));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [WebhookActivityFixtures::class];
    }
}
