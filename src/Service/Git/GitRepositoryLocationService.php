<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Entity\Repository\Repository;

class GitRepositoryLocationService
{
    public function __construct(public readonly string $cacheDirectory)
    {
    }

    public function getLocation(Repository $repository): string
    {
        $repositoryUrl  = $repository->getUrl();
        $repositoryName = self::extractRepositoryNameFromUrl((string)$repositoryUrl);

        return $this->cacheDirectory . $repositoryName . '-' . hash('sha1', (string)$repositoryUrl) . '/';
    }

    /**
     * Extracts the bare repository name from a URL or path.
     *
     * Examples:
     *   /path/to/repo.git   → repo
     *   host.xz:foo/.git    → foo
     *   https://host/repo   → repo
     *
     * This is an inline copy of czproject/git-php's Helpers::extractRepositoryNameFromUrl logic,
     * preserved verbatim so that existing cache directory names are not invalidated.
     */
    private static function extractRepositoryNameFromUrl(string $url): string
    {
        $directory = rtrim($url, '/');

        if (substr($directory, -5) === '/.git') {
            $directory = substr($directory, 0, -5);
        }

        $directory = basename($directory, '.git');

        if (($pos = strrpos($directory, ':')) !== false) {
            $directory = substr($directory, $pos + 1);
        }

        return $directory;
    }
}
