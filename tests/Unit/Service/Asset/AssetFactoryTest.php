<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Asset;

use DR\Review\Entity\User\User;
use DR\Review\Service\Asset\AssetFactory;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\Assert;

/**
 * @coversDefaultClass \DR\Review\Service\Asset\AssetFactory
 */
class AssetFactoryTest extends AbstractTestCase
{
    private AssetFactory $assetFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->assetFactory = new AssetFactory();
    }

    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $user  = new User();
        $asset = $this->assetFactory->create($user, 'mime-type', 'data');

        static::assertSame($user, $asset->getUser());
        static::assertSame('mime-type', $asset->getMimeType());
        static::assertSame('data', stream_get_contents(Assert::notNull($asset->getData())));
        static::assertEqualsWithDelta(time(), $asset->getCreateTimestamp(), 10);
    }
}
