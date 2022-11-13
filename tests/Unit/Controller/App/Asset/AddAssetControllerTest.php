<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Asset;

use DR\GitCommitNotification\Controller\App\Asset\AddAssetController;
use DR\GitCommitNotification\Controller\App\Asset\GetAssetController;
use DR\GitCommitNotification\Entity\Asset\Asset;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\Asset\AssetRepository;
use DR\GitCommitNotification\Request\Asset\AddAssetRequest;
use DR\GitCommitNotification\Service\Asset\AssetFactory;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Asset\AddAssetController
 * @covers ::__construct
 */
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

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $asset = new Asset();
        $asset->setId(123);
        $user = new User();

        $request = $this->createMock(AddAssetRequest::class);
        $request->expects(self::once())->method('getMimeType')->willReturn('mime-type');
        $request->expects(self::once())->method('getData')->willReturn('data');

        $this->expectGetUser($user);
        $this->assetFactory->expects(self::once())->method('create')->with($user, 'mime-type', 'data')->willReturn($asset);
        $this->assetRepository->expects(self::once())->method('save')->with($asset, true);
        $this->expectGenerateUrl(GetAssetController::class, ['id' => 123]);

        ($this->controller)($request);
    }

    public function getController(): AbstractController
    {
        return new AddAssetController($this->assetRepository, $this->assetFactory);
    }
}
