<?php
declare(strict_types=1);

namespace DR\Review\Controller\Api;

use DR\Review\Entity\Repository\Repository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UploadTestCoverage
{
    #[Route('/api/test-coverage', name: self::class, methods: 'POST')]
    public function __invoke(Repository $repository): JsonResponse
    {
        return new JsonResponse(
            [

            ]
        );
    }

}
