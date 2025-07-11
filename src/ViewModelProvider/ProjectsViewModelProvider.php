<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\DBAL\Exception;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;

readonly class ProjectsViewModelProvider implements ProviderInterface
{
    public function __construct(private RepositoryRepository $repositoryRepository, private RevisionRepository $revisionRepository)
    {
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ProjectsViewModel
    {
        $repositories  = $this->repositoryRepository->findBy(['active' => 1], ['displayName' => 'ASC']);
        $revisionCount = $this->revisionRepository->getRepositoryRevisionCount();

        return new ProjectsViewModel($repositories, $revisionCount);
    }
}
