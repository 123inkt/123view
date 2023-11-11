<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Config;

use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\RuleNotificationFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(RuleNotificationRepository::class)]
class RuleNotificationRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testGetUnreadNotificationCountForUser(): void
    {
        $user       = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $repository = self::getService(RuleNotificationRepository::class);

        static::assertSame(1, $repository->getUnreadNotificationCountForUser($user));
    }

    /**
     * @throws Throwable
     */
    public function testGetUnreadNotificationPerRuleCount(): void
    {
        $repository = self::getService(RuleNotificationRepository::class);
        $user       = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $rule       = Assert::notNull(self::getService(RuleRepository::class)->findOneBy(['name' => 'name']));

        $expected = [$rule->getId() => 1];
        static::assertSame($expected, $repository->getUnreadNotificationPerRuleCount($user));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RuleNotificationFixtures::class];
    }
}
