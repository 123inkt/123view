<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\Helpers;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Utility\CircuitBreaker;
use League\Uri\Http;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Stopwatch\Stopwatch;
use Throwable;

/**
 * Service to clone or pull the repository from the given url.
 */
class GitRepositoryService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private string         $cacheDirectory;
    private CircuitBreaker $circuitBreaker;

    public function __construct(
        private readonly Git $git,
        private readonly Filesystem $filesystem,
        private readonly ?Stopwatch $stopwatch,
        string $cacheDirectory
    ) {
        $this->circuitBreaker = new CircuitBreaker(5, 5000);
        $this->cacheDirectory = $cacheDirectory . '/git/';
    }

    /**
     * @throws RepositoryException
     */
    public function getRepository(string $repositoryUrl): GitRepository
    {
        try {
            return $this->circuitBreaker->execute(fn() => $this->tryGetRepository($repositoryUrl));
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
    private function tryGetRepository(string $repositoryUrl): GitRepository
    {
        // create cache directory
        $this->filesystem->mkdir($this->cacheDirectory);

        $repositoryName = Helpers::extractRepositoryNameFromUrl($repositoryUrl);
        $repositoryDir  = $this->cacheDirectory . $repositoryName . '-' . hash('sha1', $repositoryUrl) . '/';

        if ($this->filesystem->exists($repositoryDir . '.git') === false) {
            // is new repository
            $this->stopwatch?->start('repository.clone', 'git');
            $this->logger?->info(sprintf('git: clone repository `%s`.', Http::createFromString($repositoryUrl)->withUserInfo('', '')));
            $this->git->cloneRepository($repositoryUrl, $repositoryDir);
            $this->stopwatch?->stop('repository.clone');
        }

        return new GitRepository($this->stopwatch, $repositoryDir);
    }
}
