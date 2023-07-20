<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Webhook;

use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\RepositoryFixtures;
use DR\Review\Tests\DataFixtures\WebhookFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebhookRepository::class)]
class WebhookRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testFindByRepositoryId(): void
    {
        $repository        = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $webhookRepository = self::getService(WebhookRepository::class);

        static::assertCount(0, $webhookRepository->findByRepositoryId(-1));
        static::assertCount(1, $webhookRepository->findByRepositoryId((int)$repository->getId()));
    }

    /**
     * @throws Exception
     */
    public function testFindByRepositoryIdWithEnabled(): void
    {
        $repository        = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $webhookRepository = self::getService(WebhookRepository::class);

        static::assertCount(1, $webhookRepository->findByRepositoryId((int)$repository->getId(), true));
        static::assertCount(0, $webhookRepository->findByRepositoryId((int)$repository->getId(), false));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [WebhookFixtures::class, RepositoryFixtures::class];
    }
}
