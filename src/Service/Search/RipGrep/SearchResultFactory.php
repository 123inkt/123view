<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Utils\Arrays;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo;

class SearchResultFactory
{
    public function __construct(private readonly GitRepositoryLocationService $locationService)
    {
    }

    /**
     * @param Repository[] $repositories
     */
    public function create(string $filepath, string $basepath, array $repositories): ?SearchResult
    {
        $filepath         = Path::normalize($filepath);
        $rootDirectory    = Arrays::first(explode('/', $filepath, 2));
        $relativeFilepath = Path::makeRelative($filepath, $rootDirectory);

        $repository = $this->getRepository($basepath . $rootDirectory, $repositories);
        if ($repository === null) {
            return null;
        }

        return new SearchResult($repository, new SplFileInfo($filepath, $basepath . $rootDirectory, $relativeFilepath));
    }

    private function getRepository(string $directory, array $repositories): ?Repository
    {
        foreach ($repositories as $repository) {
            $location = rtrim(Path::normalize($this->locationService->getLocation($repository)), '/');
            if ($directory === $location) {
                return $repository;
            }
        }

        return null;
    }
}
