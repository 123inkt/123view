<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Asset;

use DR\Review\Entity\Asset\Asset;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GetAssetController
{
    #[Route('app/assets/{id<\d+>}', name: self::class, methods: 'GET')]
    #[IsGranted(
        new Expression('is_granted("ROLE_USER") or is_granted("VIEW", subject)'),
        new Expression('args["asset"]')
    )]
    public function __invoke(#[MapEntity] Asset $asset): Response
    {
        return (new Response($asset->getData(), 200, ['Content-Type' => $asset->getMimeType()]))->setPublic();
    }
}
