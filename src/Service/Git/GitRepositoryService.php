<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryUtil;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Git\GitRepositoryFactory;
use DR\Review\Utility\CircuitBreaker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
        private readonly Filesystem $filesystem,
        private readonly ?Stopwatch $stopwatch,
        private readonly GitRepositoryLocationService $locationService,
        private readonly GitCommandBuilderFactory $commandBuilderFactory,
        private readonly GitRepositoryFactory $repositoryFactory,
        private readonly GitRepositoryLockManager $lockManager,
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
        } catch (Throwable $exception) {
            if ($exception instanceof RepositoryException) {
                throw $exception;
            }

            throw new RepositoryException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws RepositoryException
     */
    private function tryGetRepository(Repository $repository): GitRepository
    {
        $repositoryDir = $this->locationService->getLocation($repository);
        $canonicalDir  = rtrim($repositoryDir, '/\\');
        $parentDir     = dirname($canonicalDir);

        // ensure parent cache directory exists
        $this->filesystem->mkdir($parentDir);

        if ($this->filesystem->exists($repositoryDir . '.git')) {
            return $this->repositoryFactory->create($this->gitLogger, $repository, $this->stopwatch, $repositoryDir);
        }

        // Initial clone: require caller to hold the repository lock to prevent concurrent clones
        if ($this->lockManager->lockAcquired($repository) === false) {
            throw new RepositoryException(
                sprintf('git: clone of `%s` must be performed inside a repository lock.', $repository->getName())
            );
        }

        // Re-check after acquiring the in-process lock guard (another process may have cloned while we waited).
        // @phpstan-ignore if.alwaysFalse (filesystem::exists() is impure — result can change between calls)
        if ($this->filesystem->exists($repositoryDir . '.git')) {
            return $this->repositoryFactory->create($this->gitLogger, $repository, $this->stopwatch, $repositoryDir);
        }

        $tempDir = $canonicalDir . '.tmp';
        $this->filesystem->remove($tempDir);

        $this->stopwatch?->start('repository.clone', 'git');
        $this->gitLogger->info(
            'git: clone repository `{url}`.',
            ['url' => (string)$repository->getUrl()->withUserInfo(null)]
        );

        $cloneUrl     = (string)RepositoryUtil::getUriWithCredentials($repository);
        $cloneBuilder = $this->commandBuilderFactory->createClone()
            ->repository($cloneUrl)
            ->directory($tempDir);

        // Use the parent directory as working directory for the bootstrap executor
        $bootstrapRepo = $this->repositoryFactory->create(
            $this->gitLogger,
            $repository,
            $this->stopwatch,
            $parentDir . '/'
        );

        try {
            $bootstrapRepo->execute($cloneBuilder, false, null);
            $this->stopwatch?->stop('repository.clone');
        } catch (ProcessFailedException $exception) {
            $this->stopwatch?->stop('repository.clone');
            $this->filesystem->remove($tempDir);

            $stderr       = trim($exception->getProcess()->getErrorOutput());
            $exitCode     = $exception->getProcess()->getExitCode() ?? 1;
            $safeMessage  = 'git: clone failed (exit ' . $exitCode . ')';
            if ($stderr !== '') {
                // Apply redactions before including stderr in the exception message
                foreach ($cloneBuilder->getSensitiveReplacements() as $search => $replace) {
                    $stderr = str_replace($search, $replace, $stderr);
                }
                $safeMessage .= ': ' . $stderr;
            }

            throw new RepositoryException($safeMessage, $exitCode);
        }

        // Atomically place the completed clone at the final location
        $this->filesystem->rename($tempDir, $canonicalDir);

        return $this->repositoryFactory->create($this->gitLogger, $repository, $this->stopwatch, $repositoryDir);
    }
}
