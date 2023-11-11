<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Utils\Assert;

class RuleNotificationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $rule = Assert::notNull($manager->getRepository(Rule::class)->findOneBy(['name' => 'name']));

        $notification = new RuleNotification();
        $notification->setRule($rule);
        $notification->setRead(true);
        $notification->setNotifyTimestamp(123);
        $notification->setCreateTimestamp(456);
        $manager->persist($notification);

        $notification = new RuleNotification();
        $notification->setRule($rule);
        $notification->setRead(false);
        $notification->setNotifyTimestamp(789);
        $notification->setCreateTimestamp(102);
        $manager->persist($notification);

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RuleFixtures::class];
    }
}
