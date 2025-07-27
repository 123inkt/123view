<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Revision;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[Get(
    '/view-model/revisions/{repositoryId}',
    uriVariables        : 'repositoryId',
    requirements        : ['repositoryId' => '\d+'],
    openapi             : new OpenApiOperation(tags: ['ViewModel']),
    normalizationContext: ['groups' => ['app:revisions', 'app:paginator', 'repository:read', 'revision:read']],
    security            : 'is_granted("ROLE_USER")',
    provider            : RevisionViewModelProvider::class
)]
class RevisionsViewModel
{
    /**
     * @param Paginator<Revision>          $revisions
     * @param PaginatorViewModel<Revision> $paginator
     */
    public function __construct(
        #[Groups('app:revisions')]
        public readonly Repository $repository,
        private readonly Paginator $revisions,
        #[Groups('app:revisions')]
        public readonly PaginatorViewModel $paginator,
        public readonly string $searchQuery
    ) {
    }

    /**
     * @return Revision[]
     */
    #[Groups('app:revisions')]
    public function getRevisions(): array
    {
        return iterator_to_array($this->revisions);
    }
}
