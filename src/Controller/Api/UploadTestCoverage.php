<?php
declare(strict_types=1);

namespace DR\Review\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UploadTestCoverage
{
    #[Route('/api/test-coverage', name: self::class, methods: ['GET', 'POST'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(
            [
                'repository' => 'test'

            ]
        );
    }

}
