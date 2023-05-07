<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Entity\Webhook\WebhookActivity;
use DR\Review\Utility\Assert;

class WebhookActivityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $webhook = Assert::notNull($manager->getRepository(Webhook::class)->findOneBy(['url' => 'url']));

        $activity = new WebhookActivity();
        $activity->setWebhook($webhook);
        $activity->setCreateTimestamp(123456789);
        $activity->setStatusCode(200);
        $activity->setResponse('response');
        $activity->setRequest('request');

        $manager->persist($activity);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [WebhookFixtures::class];
    }
}
