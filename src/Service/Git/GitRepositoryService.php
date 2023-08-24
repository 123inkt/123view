<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\Helpers;
use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Utility\CircuitBreaker;
use DR\Utils\Assert;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Stopwatch\Stopwatch;
use Throwable;

/**
 * Service to clone or pull the repository from the given url.
 */
class GitRepositoryService
{
    private readonly string         $cacheDirectory;
    private readonly CircuitBreaker $circuitBreaker;

    public function __construct(
        private readonly LoggerInterface $gitLogger,
        private readonly Git $git,
        private readonly Filesystem $filesystem,
        private readonly ?Stopwatch $stopwatch,
        string $cacheDirectory
    ) {
        $this->circuitBreaker = new CircuitBreaker(5, 5000);
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * @throws RepositoryException
     */
    public function getRepository(Repository $repository): GitRepository
    {
        try {
            return $this->circuitBreaker->execute(fn() => $this->tryGetRepository($repository));
        } catch (GitException $exception) {
            $message = $exception->getMessage() . ': ';
            if ($exception->getRunnerResult() !== null) {
                $message .= implode(" ", $exception->getRunnerResult()->getErrorOutput());
            }

            throw new RepositoryException($message, $exception->getCode(), $exception);
        } catch (Throwable $exception) {
            throw new RepositoryException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws GitException
     */
    private function tryGetRepository(Repository $repository): GitRepository
    {
        // create cache directory
        $this->filesystem->mkdir($this->cacheDirectory);

        $repositoryUrl  = Assert::notNull($repository->getUrl());
        $repositoryName = Helpers::extractRepositoryNameFromUrl((string)$repositoryUrl);
        $repositoryDir  = $this->cacheDirectory . $repositoryName . '-' . hash('sha1', (string)$repositoryUrl) . '/';

        // add credentials
        $credentials = $repository->getCredential()?->getCredentials();
        if ($credentials instanceof BasicAuthCredential) {
            $repositoryUrl = $repositoryUrl->withUserInfo($credentials->getUsername(), $credentials->getPassword());
        }

        if ($this->filesystem->exists($repositoryDir . '.git') === false) {
            // is new repository
            $this->stopwatch?->start('repository.clone', 'git');
            $this->gitLogger->info(sprintf('git: clone repository `%s`.', $repositoryUrl->withUserInfo(null)));
            $this->git->cloneRepository((string)$repositoryUrl, $repositoryDir);
            $this->stopwatch?->stop('repository.clone');
        }

        return new GitRepository($this->gitLogger, $repository, $this->stopwatch, $repositoryDir);
    }
}
