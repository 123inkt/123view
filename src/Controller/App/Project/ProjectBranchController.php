<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\RevList\GitRevListService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProjectBranchController
{
    public function __construct(
        private readonly GitRevListService $revListService
    ) {
    }

    /**
     * @throws RepositoryException
     */
    #[Route('app/projects/{id<\d+>}/branch', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] Repository $repository): Response
    {
        $branchName = $request->query->get('branch', '');

        $hashes = $this->revListService->getCommitsAheadOfMaster($repository, $branchName);

        return new JsonResponse($hashes);
    }
}
