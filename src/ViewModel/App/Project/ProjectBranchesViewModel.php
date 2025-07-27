<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Project;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\ViewModelProvider\ProjectBranchesViewModelProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[Get(
    '/view-model/branches/{repositoryId}',
    uriVariables        : 'repositoryId',
    requirements        : ['repositoryId' => '\d+'],
    openapi             : new OpenApiOperation(tags: ['ViewModel']),
    normalizationContext: ['groups' => ['app:branches', 'repository:read', 'code-review:read']],
    security            : 'is_granted("ROLE_USER")',
    provider            : ProjectBranchesViewModelProvider::class
)]
// TODO angular remove non angular methods and implement only the provide method
class ProjectBranchesViewModel
{
    /**
     * @param string[]                  $branches
     * @param string[]                  $mergedBranches
     * @param array<string, CodeReview> $reviews
     */
    public function __construct(
        #[Groups('app:branches')]
        public readonly Repository $repository,
        public readonly ?string $searchQuery,
        #[Groups('app:branches')]
        public readonly array $branches,
        #[Groups('app:branches')]
        public readonly array $mergedBranches,
        #[Groups('app:branches')]
        public readonly array $reviews
    ) {
    }

    public function getReview(string $branchName): ?CodeReview
    {
        return $this->reviews[$branchName] ?? null;
    }

    public function isMerged(string $branchName): bool
    {
        return in_array($branchName, $this->mergedBranches, true);
    }
}
