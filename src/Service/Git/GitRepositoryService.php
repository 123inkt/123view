<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryUtil;
use DR\Review\Exception\RepositoryException;
use DR\Review\Model\Git\GitRepository;
use DR\Review\Service\Git\Clone\GitCloneService;
use DR\Review\Service\Git\Ssh\GitSshSetupService;
use DR\Review\Utility\CircuitBreaker;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

/**
 * Service to clone or pull the repository from the given url.
 */
readonly class GitRepositoryService
{
    private CircuitBreaker $circuitBreaker;

    public function __construct(
        #[Target('gitLogger')] private Filesystem $filesystem,
        private GitRepositoryLocationService $locationService,
        private GitRepositoryFactory $repositoryFactory,
        private GitRepositoryLockManager $lockManager,
        private GitSshSetupService $sshSetupService,
        private GitCloneService $cloneService,
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
        } catch (RepositoryException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
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
            return $this->repositoryFactory->create($repository, $repositoryDir);
        }

        // Initial clone: require caller to hold the repository lock to prevent concurrent clones
        if ($this->lockManager->lockAcquired($repository) === false) {
            throw new RepositoryException(sprintf('git: clone of `%s` must be performed inside a repository lock.', $repository->getName()));
        }

        // Re-check after acquiring the in-process lock guard (another process may have cloned while we waited).
        // @phpstan-ignore if.alwaysFalse (filesystem::exists() is impure — result can change between calls)
        if ($this->filesystem->exists($repositoryDir . '.git')) {
            return $this->repositoryFactory->create($repository, $repositoryDir);
        }

        $tempDir = $canonicalDir . '.tmp';
        $this->filesystem->remove($tempDir);

        $credential = $repository->getCredential();
        if ($credential !== null && $credential->getAuthType() === AuthenticationType::SSH_KEY) {
            $this->sshSetupService->withSshAuth(
                $credential,
                fn(array $env): null => $this->cloneService->clone($repository, $repository->getUrl(), $tempDir, $env)
            );
        } else {
            $this->cloneService->clone($repository, RepositoryUtil::getUriWithCredentials($repository), $tempDir);
        }

        // Atomically place the completed clone at the final location
        $this->filesystem->rename($tempDir, $canonicalDir);

        return $this->repositoryFactory->create($repository, $repositoryDir);
    }
}
