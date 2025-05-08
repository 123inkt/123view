<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\Helpers;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryUtil;
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
    private readonly CircuitBreaker $circuitBreaker;

    public function __construct(
        private readonly LoggerInterface $gitLogger,
        private readonly Git $git,
        private readonly Filesystem $filesystem,
        private readonly ?Stopwatch $stopwatch,
        private readonly GitRepositoryLocationService $locationService
    ) {
        $this->circuitBreaker = new CircuitBreaker(5, 5000);
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
        $repositoryDir  = $this->locationService->getLocation($repository);

        // create cache directory
        $this->filesystem->mkdir(dirname($repositoryDir));

        if ($this->filesystem->exists($repositoryDir . '.git') === false) {
            // is new repository
            $this->stopwatch?->start('repository.clone', 'git');
            $this->gitLogger->info(sprintf('git: clone repository `%s`.', $repository->getUrl()->withUserInfo(null)));
            $this->git->cloneRepository((string)RepositoryUtil::getUriWithCredentials($repository), $repositoryDir);
            $this->stopwatch?->stop('repository.clone');
        }

        return new GitRepository($this->gitLogger, $repository, $this->stopwatch, $repositoryDir);
    }
}
