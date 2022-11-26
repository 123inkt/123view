<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\Config;

use DR\GitCommitNotification\Entity\Notification\Frequency;
use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Entity\Notification\RuleOptions;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;
use DR\GitCommitNotification\Tests\DataFixtures\UserFixtures;
use DR\GitCommitNotification\Utility\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\Config\RuleRepository
 * @covers ::__construct
 */
class RuleRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::getActiveRulesForFrequency
     * @throws Exception
     */
    public function testGetActiveRulesForFrequency(): void
    {
        $user       = Assert::notNull(static::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $repository = static::getService(RuleRepository::class);

        $rule = new Rule();
        $rule->setUser($user);
        $rule->setName('rule');
        $rule->setActive(true);
        $rule->setRuleOptions((new RuleOptions())->setFrequency(Frequency::ONCE_PER_DAY));
        $repository->save($rule, true);

        static::assertCount(1, $repository->getActiveRulesForFrequency(true, Frequency::ONCE_PER_DAY));
        static::assertCount(0, $repository->getActiveRulesForFrequency(false, Frequency::ONCE_PER_DAY));
        static::assertCount(0, $repository->getActiveRulesForFrequency(true, Frequency::ONCE_PER_FOUR_HOURS));
        static::assertCount(0, $repository->getActiveRulesForFrequency(false, Frequency::ONCE_PER_FOUR_HOURS));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class];
    }
}
