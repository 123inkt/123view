<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Asset;

use DR\Review\Entity\Asset\Asset;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Twig\Attribute\Entity;
use Symfony\Bridge\Twig\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetAssetController
{
    #[Route('app/assets/{id<\d+>}', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('asset')]
    public function __invoke(Asset $asset): Response
    {
        /** @var resource $data */
        $data = $asset->getData();

        return (new Response((string)stream_get_contents($data), 200, ['Content-Type' => $asset->getMimeType()]))->setPublic();
    }
}
