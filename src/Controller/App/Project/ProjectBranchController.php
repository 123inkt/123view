<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Revision\RevisionRepository;
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
        private readonly GitRevListService $revListService,
        private readonly RevisionRepository $revisionRepository,
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
        $hashes     = $this->revListService->getCommitsAheadOfMaster($repository, $branchName);
        $revisions  = $this->revisionRepository->findBy(['repository' => $repository, 'commitHash' => $hashes], ['createTimestamp' => 'ASC']);

        $revHashes = array_map(static fn(Revision $revision) => (string)$revision->getCommitHash(), $revisions);

        return new JsonResponse(['hashes' => $hashes, 'revHashes' => $revHashes]);
    }
}
