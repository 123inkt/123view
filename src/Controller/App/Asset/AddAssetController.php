<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Asset;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Asset\AssetRepository;
use DR\Review\Request\Asset\AddAssetRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Asset\AssetFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AddAssetController extends AbstractController
{
    public function __construct(private readonly AssetRepository $assetRepository, private readonly AssetFactory $assetFactory)
    {
    }

    #[Route('app/assets', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(AddAssetRequest $request): JsonResponse
    {
        // create entity
        $asset = $this->assetFactory->create($this->getUser(), $request->getMimeType(), $request->getData());

        // save entity
        $this->assetRepository->save($asset, true);

        return new JsonResponse(['url' => $this->generateUrl(GetAssetController::class, ['id' => $asset->getId(), 'hash' => $asset->getHash()])]);
    }
}
