<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLocationService;
use Symfony\Component\Filesystem\Path;

class CodeOwnerFileFinder
{
    public function __construct(private GitRepositoryLocationService $locationService)
    {
    }

    /**
     * @return list<string>
     */
    public function find(Repository $repository, string $filepath): array
    {
        $filepath  = Path::normalize($filepath);
        $directory = rtrim(Path::normalize($this->locationService->getLocation($repository)), '/');

        $results = [];
        while (true) {
            $filepath = dirname($filepath);
            if ($filepath === '' || $filepath === '.') {
                break;
            }

            $codeowners = $directory . '/' . $filepath . '/CODEOWNERS';
            if (file_exists($codeowners)) {
                $results[] = $codeowners;
            }
        }

        $rootCodeowners = $directory . '/CODEOWNERS';
        if (file_exists($rootCodeowners)) {
            $results[] = $rootCodeowners;
        }

        return $results;
    }
}
