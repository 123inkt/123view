<?php
declare(strict_types=1);

namespace DR\Review\Controller\Api\Report;

use DR\Review\Controller\AbstractController;
use DR\Review\Request\Report\UploadCodeInspectionRequest;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UploadCodeCoverageController extends AbstractController
{
    #[Route('/api/report/code-coverage/{repositoryName<[a-z0-9-]+>}/{commitHash<[a-zA-Z0-9]{6,255}>}', name: self::class, methods: ['GET', 'POST'])]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(UploadCodeInspectionRequest $request, string $repositoryName, string $commitHash): Response
    {
        return new JsonResponse(
            [
                'repository' => 'test'
            ]
        );
    }
}
