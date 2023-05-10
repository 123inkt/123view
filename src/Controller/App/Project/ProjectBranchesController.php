<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectBranchesController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly GitBranchService $branchService)
    {
    }

    /**
     * @return array<string, string|ProjectBranchesViewModel>
     * @throws RepositoryException
     */
    #[Route('app/projects/{id<\d+>}/branches', name: self::class, methods: 'GET')]
    #[Template('app/repository/branches.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] Repository $repository): array
    {
        $branches       = $this->branchService->getRemoteBranches($repository);
        $mergedBranches = $this->branchService->getRemoteBranches($repository, true);
        $model          = new ProjectBranchesViewModel($repository, $branches, $mergedBranches);

        return [
            'page_title'        => $this->translator->trans('branches'),
            'branchesViewModel' => $model
        ];
    }
}
