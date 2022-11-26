<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Webhook;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Webhook\Webhook
 * @covers ::__construct
 */
class WebhookTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getActivities']);
        static::assertAccessorPairs(Webhook::class, $config);
    }

    /**
     * @covers ::getActivities
     * @covers ::setActivities
     */
    public function testReviewers(): void
    {
        $collection = new ArrayCollection();

        $webhook = new Webhook();
        static::assertInstanceOf(ArrayCollection::class, $webhook->getActivities());

        $webhook->setActivities($collection);
        static::assertSame($collection, $webhook->getActivities());
    }
}
