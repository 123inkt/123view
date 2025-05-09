<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use CzProject\GitPhp\Helpers;
use DR\Review\Entity\Repository\Repository;

class GitRepositoryLocationService
{
    public function __construct(public readonly string $cacheDirectory)
    {
    }

    public function getLocation(Repository $repository): string
    {
        $repositoryUrl  = $repository->getUrl();
        $repositoryName = Helpers::extractRepositoryNameFromUrl((string)$repositoryUrl);

        return $this->cacheDirectory . $repositoryName . '-' . hash('sha1', (string)$repositoryUrl) . '/';
    }
}
