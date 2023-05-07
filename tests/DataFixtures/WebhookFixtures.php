<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Utility\Assert;

class WebhookFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $repository = Assert::notNull($manager->getRepository(Repository::class)->findOneBy(['name' => 'repository']));

        $webhook = new Webhook();
        $webhook->setUrl('url');
        $webhook->setEnabled(true);
        $webhook->setHeaders(['foo' => 'bar']);
        $webhook->setRetries(3);
        $webhook->setVerifySsl(true);
        $webhook->getRepositories()->add($repository);

        $manager->persist($webhook);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RepositoryFixtures::class];
    }
}
