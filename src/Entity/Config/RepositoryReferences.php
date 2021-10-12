<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RepositoryReferences
{
    /**
     * @SerializedName("repository")
     * @var RepositoryReference[]
     */
    private array $repositories = [];

    /**
     * @return RepositoryReference[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }

    public function addRepository(RepositoryReference $repository): void
    {
        $this->repositories[] = $repository;
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeRepository(RepositoryReference $repository): void
    {
        // method only required for deserialization
    }
}
