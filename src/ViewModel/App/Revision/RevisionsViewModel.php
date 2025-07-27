<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Revision;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[Get(
    '/view-model/revisions/{repositoryId}',
    uriVariables        : 'repositoryId',
    requirements        : ['repositoryId' => '\d+'],
    openapi             : new OpenApiOperation(tags: ['ViewModel']),
    normalizationContext: ['groups' => ['app:revisions', 'app:paginator', 'repository:read', 'revision:read', 'code-review:read']],
    security            : 'is_granted("ROLE_USER")',
    provider            : RevisionViewModelProvider::class
)]
class RevisionsViewModel
{
    /**
     * @param array<Revision>              $revisions
     * @param array<int, int>              $reviewIds [revisionId => review projectId]
     * @param PaginatorViewModel<Revision> $paginator
     */
    public function __construct(
        #[Groups('app:revisions')]
        public readonly Repository $repository,
        #[Groups('app:revisions')]
        public readonly array $revisions,
        #[Groups('app:revisions')]
        public readonly array $reviewIds,
        #[Groups('app:revisions')]
        public readonly PaginatorViewModel $paginator,
        public readonly string $searchQuery = ''
    ) {
    }
}
