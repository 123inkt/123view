<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Mercure;

use DR\Review\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Discovery;

class DiscoverController extends AbstractController
{
    public function publish(Request $request, Discovery $discovery, Authorization $authorization): JsonResponse
    {
        $discovery->addLink($request);
        $authorization->setCookie($request, ['https://example.com/books/1']);

        return $this->json(
            [
                '@id'          => '/demo/books/1',
                'availability' => 'https://schema.org/InStock'
            ]
        );
    }
}
