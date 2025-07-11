<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Project;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use DR\Review\Entity\Repository\Repository;
use DR\Review\ViewModel\ViewModelInterface;
use DR\Review\ViewModelProvider\ProjectsViewModelProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[Get(
    '/view-model/projects',
    openapi             : new OpenApiOperation(tags: ['ViewModel']),
    normalizationContext: ['groups' => ['app:projects', 'repository:read']],
    security            : 'is_granted(ROLE_USER)',
    provider: ProjectsViewModelProvider::class
)]
class ProjectsViewModel implements ViewModelInterface
{
    /**
     * @param Repository[]    $repositories
     * @param array<int, int> $revisionCount
     */
    public function __construct(
        #[Groups('app:projects')] public readonly array $repositories,
        #[Groups('app:projects')] public readonly array $revisionCount,
    ) {
    }
}
