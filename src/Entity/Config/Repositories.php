<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use DR\GitCommitNotification\Exception\ConfigException;
use Symfony\Component\Serializer\Annotation\SerializedName;

class Repositories
{
    /**
     * @SerializedName("repository")
     * @var Repository[]
     */
    private array $repositories = [];

    /**
     * @return Repository[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }

    public function addRepository(Repository $repository): void
    {
        $this->repositories[] = $repository;
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeRepository(Repository $repository): void
    {
        // method only required for deserialization
    }

    /**
     * @throws ConfigException
     */
    public function getByReference(RepositoryReference $reference): Repository
    {
        foreach ($this->repositories as $repository) {
            if ($repository->name === $reference->name) {
                return $repository;
            }
        }

        throw new ConfigException(sprintf('No repository configured with name `%s`', $reference->name));
    }
}
