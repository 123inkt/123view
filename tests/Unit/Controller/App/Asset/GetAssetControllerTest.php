<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Asset;

use DR\GitCommitNotification\Controller\App\Asset\GetAssetController;
use DR\GitCommitNotification\Entity\Asset\Asset;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Asset\GetAssetController
 * @covers ::__construct
 */
class GetAssetControllerTest extends AbstractTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /** @var resource $stream */
        $stream = fopen('php://memory', 'rb+');
        fwrite($stream, 'image-data');
        rewind($stream);

        $asset = new Asset();
        $asset->setMimeType('image/png');
        $asset->setData($stream);

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
