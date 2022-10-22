<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Asset;

use DR\GitCommitNotification\Entity\Asset\Asset;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetAssetController
{
    #[Route('app/assets/{id<\d+>}', name: self::class, methods: 'GET')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('asset')]
    public function __invoke(Asset $asset): Response
    {
        return (new Response($asset->getData(), 200, ['Content-Type' => $asset->getMimeType()]))->setPublic();
    }
}
