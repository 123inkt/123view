<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryUtil;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Service\Git\Ssh\GitSshSetupService;
use DR\Review\Utility\CircuitBreaker;
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
        private readonly GitRepositoryLocationService $locationService,
        private readonly GitSshSetupService $sshSetupService,
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
        $repositoryDir = $this->locationService->getLocation($repository);

        // create cache directory
        $this->filesystem->mkdir(dirname($repositoryDir));

        if ($this->filesystem->exists($repositoryDir . '.git') === false) {
            $this->stopwatch?->start('repository.clone', 'git');
            $this->gitLogger->info(sprintf('git: clone repository `%s`.', $repository->getUrl()->withUserInfo(null)));

            $credential = $repository->getCredential();
            if ($credential !== null && $credential->getAuthType() === AuthenticationType::SSH_KEY) {
                $this->sshSetupService->withSshAuth($credential, function (array $env) use ($repository, $repositoryDir): void {
                    putenv('GIT_SSH_COMMAND=' . $env['GIT_SSH_COMMAND']);
                    try {
                        $this->git->cloneRepository((string)$repository->getUrl(), $repositoryDir);
                    } finally {
                        putenv('GIT_SSH_COMMAND'); // unset after clone
                    }
                });
            } else {
                $this->git->cloneRepository((string)RepositoryUtil::getUriWithCredentials($repository), $repositoryDir);
            }

            $this->stopwatch?->stop('repository.clone');
        }

        return new GitRepository($this->gitLogger, $repository, $this->stopwatch, $repositoryDir);
    }
}
