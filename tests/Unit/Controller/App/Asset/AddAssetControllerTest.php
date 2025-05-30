<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Asset;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Asset\AddAssetController;
use DR\Review\Controller\App\Asset\GetAssetController;
use DR\Review\Entity\Asset\Asset;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Asset\AssetRepository;
use DR\Review\Request\Asset\AddAssetRequest;
use DR\Review\Service\Asset\AssetFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<AddAssetController>
 */
#[CoversClass(AddAssetController::class)]
class AddAssetControllerTest extends AbstractControllerTestCase
{
    private AssetRepository&MockObject $assetRepository;
    private AssetFactory&MockObject    $assetFactory;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepository::class);
        $this->assetFactory    = $this->createMock(AssetFactory::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $asset = new Asset();
        $asset->setId(123);
        $user = new User();

        $request = $this->createMock(AddAssetRequest::class);
        $request->expects($this->once())->method('getMimeType')->willReturn('mime-type');
        $request->expects($this->once())->method('getData')->willReturn('data');

        $this->expectGetUser($user);
        $this->assetFactory->expects($this->once())->method('create')->with($user, 'mime-type', 'data')->willReturn($asset);
        $this->assetRepository->expects($this->once())->method('save')->with($asset, true);
        $this->expectGenerateUrl(GetAssetController::class, ['id' => 123]);

        ($this->controller)($request);
    }

    public function getController(): AbstractController
    {
        return new AddAssetController($this->assetRepository, $this->assetFactory);
    }
}
