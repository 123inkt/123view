<?php
declare(strict_types=1);

namespace DR\Review\Controller\Api\Report;

use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UploadCodeInspectionController
{
    #[Route('/api/report/code-inspection/{repositoryName<[a-z0-9-]+>}/{commitHash<[a-zA-Z0-9]+>}', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(string $repositoryName, string $commitHash): JsonResponse
    {
        return new JsonResponse(
            [
                'repositoryName' => $repositoryName,
                'commitHash'     => $commitHash
            ]
        );
    }
}
