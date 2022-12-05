<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Webhook;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Webhook\Webhook
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
