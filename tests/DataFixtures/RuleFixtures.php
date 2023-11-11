<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use DR\Utils\Assert;

class RuleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = Assert::notNull($manager->getRepository(User::class)->findOneBy(['email' => 'sherlock@example.com']));

        $rule = new Rule();
        $rule->setUser($user);
        $rule->setName('name');
        $rule->setActive(true);

        $manager->persist($rule);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
