<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Asset;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Repository\Asset\AssetRepository;
use DR\GitCommitNotification\Request\Asset\AddAssetRequest;
use DR\GitCommitNotification\Service\Asset\AssetFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AddAssetController extends AbstractController
{
    public function __construct(private readonly AssetRepository $assetRepository, private readonly AssetFactory $assetFactory)
    {
    }

    #[Route('app/assets', name: self::class, methods: 'POST')]
    #[IsGranted('ROLE_USER')]
    public function __invoke(AddAssetRequest $request): JsonResponse
    {
        // create entity
        $asset = $this->assetFactory->create($this->getUser(), $request->getMimeType(), $request->getData());

        // save entity
        $this->assetRepository->save($asset, true);

        return new JsonResponse(['url' => $this->generateUrl(GetAssetController::class, ['id' => $asset->getId()])]);
    }
}
