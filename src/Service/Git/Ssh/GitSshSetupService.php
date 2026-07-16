<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Ssh;

use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Service\Repository\CredentialEncryptionService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Executes a callable with SSH authentication fully set up and torn down around it.
 *
 * Usage:
 *   $result = $this->sshSetupService->withSshAuth($credential, function (array $env): mixed {
 *       return $gitRepository->execute($builder, false, $env);
 *   });
 *
 * The callable receives the environment variables (e.g. GIT_SSH_COMMAND) to inject
 * into the git process. Temporary key and known-hosts files are created immediately
 * before calling the callable and deleted unconditionally in a finally block — the
 * caller can never forget teardown.
 */
class GitSshSetupService
{
    public function __construct(
        private readonly CredentialEncryptionService $encryptionService,
        #[Autowire(env: 'SSH_KNOWN_HOSTS_BASE64')] private readonly string $knownHostsBase64,
    ) {
    }

    /**
     * Set up SSH temp files, invoke $callback with the resulting env vars, then tear down.
     *
     * @template T
     * @param callable(array<string, string>): T $callback
     * @return T
     * @throws RuntimeException when SSH_KNOWN_HOSTS_BASE64 is invalid/empty or temp files cannot be created.
     */
    public function withSshAuth(RepositoryCredential $credential, callable $callback): mixed
    {
        $knownHosts = base64_decode($this->knownHostsBase64, strict: true);
        if ($knownHosts === false || $knownHosts === '') {
            throw new RuntimeException(
                'SSH_KNOWN_HOSTS_BASE64 must be a non-empty base64-encoded known-hosts string.'
            );
        }

        $privateKey     = $this->encryptionService->decrypt($credential->getValue());
        $keyFile        = $this->writeTempFile('dr_ssh_key_', $privateKey);
        $knownHostsFile = $this->writeTempFile('dr_known_hosts_', $knownHosts);

        $sshCommand = sprintf(
            'ssh -i %s -o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes -o UserKnownHostsFile=%s',
            escapeshellarg($keyFile),
            escapeshellarg($knownHostsFile),
        );

        try {
            return $callback(['GIT_SSH_COMMAND' => $sshCommand]);
        } finally {
            $this->deleteTempFile($keyFile);
            $this->deleteTempFile($knownHostsFile);
        }
    }

    private function writeTempFile(string $prefix, string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), $prefix);
        if ($file === false) {
            throw new RuntimeException(sprintf('Failed to create temporary file with prefix "%s".', $prefix));
        }

        file_put_contents($file, $content);
        chmod($file, 0600);

        return $file;
    }

    private function deleteTempFile(string $file): void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
