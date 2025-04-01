<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Asset;

use DR\Review\Controller\App\Asset\GetAssetController;
use DR\Review\Entity\Asset\Asset;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractControllerTestCase<GetAssetController>
 */
#[CoversClass(GetAssetController::class)]
class GetAssetControllerTest extends AbstractTestCase
{
    public function testInvoke(): void
    {
        $asset = new Asset();
        $asset->setMimeType('image/png');
        $asset->setData('image-data');

        $response = (new GetAssetController())($asset);

        static::assertSame('image-data', $response->getContent());

        $headers = $response->headers->all();
        unset($headers['date']);
        static::assertSame(
            [
                'content-type'  => ['image/png'],
                'cache-control' => ['public'],
            ],
            $headers
        );
    }
}
