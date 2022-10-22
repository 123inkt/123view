<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Asset;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Asset\Asset;
use DR\GitCommitNotification\Repository\Asset\AssetRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AddAssetController extends AbstractController
{
    private const ALLOWED_MIMES = [
        'image/png',
        'image/gif',
        'image/jpeg',
        'image/jpg'
    ];

    public function __construct(private readonly AssetRepository $assetRepository)
    {
    }

    #[Route('app/assets', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(Request $request): JsonResponse
    {
        $mimeType = $request->request->get('mimeType');
        $data     = $request->request->get('data');
        if (is_string($mimeType) === false || is_string($data) === false) {
            throw new BadRequestHttpException('invalid mime-type or data');
        }

        $mimeType = strtolower($mimeType);
        if (in_array($mimeType, self::ALLOWED_MIMES, true) === false) {
            throw new BadRequestHttpException('Unsupported mime type: ' . $mimeType);
        }

        if (strlen($data) > Asset::MAX_DATA_SIZE) {
            throw new BadRequestHttpException('Max data size reached. Data should be under 1MB');
        }

        // data should be base64 encoded
        $decodedData = base64_decode($data, true);
        if ($decodedData === false) {
            throw new BadRequestHttpException('Data is not a valid base64 encoded string');
        }

        // create stream
        /** @var resource $stream */
        $stream = fopen('php://memory', 'rb+');
        fwrite($stream, $decodedData);
        rewind($stream);

        $asset = new Asset();
        $asset->setUser($this->getUser());
        $asset->setMimeType($mimeType);
        $asset->setData($stream);
        $asset->setCreateTimestamp(time());

        $this->assetRepository->save($asset, true);

        return new JsonResponse(['url' => $this->generateUrl(GetAssetController::class, ['id' => $asset->getId()])]);
    }
}
