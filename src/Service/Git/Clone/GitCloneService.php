<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Clone;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryFactory;
use DR\Review\Service\Util\MessageSanitizer;
use League\Uri\Contracts\UriInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Stopwatch\Stopwatch;

readonly class GitCloneService
{
    public function __construct(
        private LoggerInterface $gitLogger,
        private GitCommandBuilderFactory $commandBuilderFactory,
        private GitRepositoryFactory $repositoryFactory,
        private MessageSanitizer $messageSanitizer,
        private Filesystem $filesystem,
        private ?Stopwatch $stopwatch,
    ) {
    }

    /**
     * @param array<string, string> $envs
     *
     * @throws RepositoryException
     */
    public function clone(Repository $repository, UriInterface $url, string $destination, array $envs = []): void
    {
        $this->stopwatch?->start('repository.clone', 'git');
        $this->gitLogger->info('git: clone repository `{url}`.', ['url' => (string)$url->withUserInfo(null)]);

        $cloneBuilder = $this->commandBuilderFactory->createClone()->repository((string)$url)->directory($destination);

        // Use the parent directory as working directory for the bootstrap executor
        $bootstrapRepo = $this->repositoryFactory->create($repository, dirname($destination) . '/');

        try {
            $bootstrapRepo->execute($cloneBuilder, false, $envs, null);
        } catch (ProcessFailedException $exception) {
            $this->filesystem->remove($destination);

            $exitCode    = $exception->getProcess()->getExitCode() ?? 1;
            $message     = 'git: clone failed (exit ' . $exitCode . '): ' . trim($exception->getProcess()->getErrorOutput());
            $safeMessage = $this->messageSanitizer->sanitize($message, $cloneBuilder->getSensitiveReplacements());

            throw new RepositoryException($safeMessage, $exitCode);
        } finally {
            $this->stopwatch?->stop('repository.clone');
        }
    }
}
