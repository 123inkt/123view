<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Webhook;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Webhook::class)]
class WebhookTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getActivities', 'getRepositories']);
        static::assertAccessorPairs(Webhook::class, $config);
    }

    public function testSetHeader(): void
    {
        $webhook = new Webhook();
        $webhook->setHeader('foo', '123');
        $webhook->setHeader('bar', '456');
        static::assertSame(['foo' => '123', 'bar' => '456'], $webhook->getHeaders());

        $webhook->setHeader('bar', null);
        static::assertSame(['foo' => '123'], $webhook->getHeaders());
    }

    public function testActivities(): void
    {
        $collection = new ArrayCollection();

        $webhook = new Webhook();
        static::assertInstanceOf(ArrayCollection::class, $webhook->getActivities());

        $webhook->setActivities($collection);
        static::assertSame($collection, $webhook->getActivities());
    }

    public function testRepositories(): void
    {
        $repository = new Repository();

        $webhook = new Webhook();
        static::assertCount(0, $webhook->getRepositories());

        $webhook->addRepository($repository);
        static::assertCount(1, $webhook->getRepositories());

        $webhook->removeRepository($repository);
        static::assertCount(0, $webhook->getRepositories());
    }
}
