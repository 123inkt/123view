<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\Helpers;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Git\GitRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Service to clone or pull the repository from the given url.
 */
class GitRepositoryService
{
    private Git             $git;
    private Filesystem      $filesystem;
    private LoggerInterface $log;
    private string          $cacheDirectory;

    public function __construct(LoggerInterface $log, Git $git, Filesystem $filesystem, string $cacheDirectory)
    {
        $this->log            = $log;
        $this->git            = $git;
        $this->filesystem     = $filesystem;
        $this->cacheDirectory = $cacheDirectory . '/git/';
    }

    /**
     * @throws RepositoryException
     */
    public function getRepository(string $repositoryUrl): GitRepository
    {
        try {
            return $this->tryGetRepository($repositoryUrl);
        } catch (GitException $exception) {
            $message = $exception->getMessage() . ': ';
            if ($exception->getRunnerResult() !== null) {
                $message .= implode(" ", $exception->getRunnerResult()->getErrorOutput());
            }

            throw new RepositoryException($message, $exception->getCode(), $exception);
        }
    }

    /**
     * @throws GitException
     */
    private function tryGetRepository(string $repositoryUrl): GitRepository
    {
        // create cache directory
        $this->filesystem->mkdir($this->cacheDirectory);

        $repositoryName = Helpers::extractRepositoryNameFromUrl($repositoryUrl);
        $repositoryDir  = $this->cacheDirectory . $repositoryName . '-' . hash('sha1', $repositoryUrl) . '/';

        if ($this->filesystem->exists($repositoryDir . '.git')) {
            // is existing repository
            $this->log->info(sprintf('git: open repository `%s`', $repositoryDir));
            $repository = $this->git->open($repositoryDir);
        } else {
            // is new repository
            $this->log->info(sprintf('git: init repository `%s`.', $repositoryDir));
            $repository = $this->git->init($repositoryDir);
            $this->log->info(sprintf('git: add origin: `%s`.', $repositoryUrl));
            $repository->addRemote('origin', $repositoryUrl);
        }

        $this->log->info(sprintf('git: fetch --all (%s)', $repositoryUrl));
        $repository->fetch(null, ['--all']);

        return new GitRepository($repositoryDir);
    }
}
