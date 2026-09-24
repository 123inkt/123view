<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Ssh;

use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Service\Git\Ssh\GitSshSetupService;
use DR\Review\Service\Repository\CredentialEncryptionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

#[CoversClass(GitSshSetupService::class)]
class GitSshSetupServiceTest extends AbstractTestCase
{
    private CredentialEncryptionService&MockObject $encryptionService;
    private string                                 $knownHostsBase64;
    private GitSshSetupService                     $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encryptionService = $this->createMock(CredentialEncryptionService::class);
        $this->knownHostsBase64  = base64_encode("github.com ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC\n");
        $this->service           = new GitSshSetupService($this->encryptionService, $this->knownHostsBase64);
    }

    public function testWithSshAuthInvokesCallback(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:encrypted');
        $this->encryptionService->expects($this->once())->method('decrypt')->willReturn('fake-key');

        $called = false;
        $this->service->withSshAuth($credential, static function (array $env) use (&$called): void {
            $called = true;
        });

        static::assertTrue($called);
    }

    public function testWithSshAuthPassesGitSshCommandToCallback(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:encrypted');
        $this->encryptionService->expects($this->once())->method('decrypt')->willReturn('fake-key');

        $receivedEnv = [];
        $this->service->withSshAuth($credential, static function (array $env) use (&$receivedEnv): void {
            $receivedEnv = $env;
        });

        static::assertArrayHasKey('GIT_SSH_COMMAND', $receivedEnv);
        $cmd = $receivedEnv['GIT_SSH_COMMAND'];
        static::assertStringContainsString('BatchMode=yes', $cmd);
        static::assertStringContainsString('IdentitiesOnly=yes', $cmd);
        static::assertStringContainsString('StrictHostKeyChecking=yes', $cmd);
        static::assertStringContainsString('UserKnownHostsFile=', $cmd);
    }

    public function testWithSshAuthWritesKeyFileAndDeletesAfter(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:encrypted');
        $this->encryptionService->expects($this->once())->method('decrypt')->willReturn('private-key-content');

        $keyFileDuringCallback = null;
        $this->service->withSshAuth($credential, static function (array $env) use (&$keyFileDuringCallback): void {
            $keyFileDuringCallback = self::extractPathFromSshCommand($env['GIT_SSH_COMMAND'], '-i');
            static::assertNotNull($keyFileDuringCallback);
            static::assertStringContainsString('private-key-content', (string)file_get_contents($keyFileDuringCallback));
        });

        static::assertNotNull($keyFileDuringCallback);
        static::assertFileDoesNotExist($keyFileDuringCallback, 'Key file must be deleted after withSshAuth returns.');
    }

    public function testWithSshAuthDeletesTempFilesAfterSuccess(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:encrypted');
        $this->encryptionService->expects($this->once())->method('decrypt')->willReturn('fake-key');

        $keyFile        = null;
        $knownHostsFile = null;

        $this->service->withSshAuth($credential, static function (array $env) use (&$keyFile, &$knownHostsFile): void {
            $keyFile        = self::extractPathFromSshCommand($env['GIT_SSH_COMMAND'], '-i');
            $knownHostsFile = self::extractPathFromSshCommand($env['GIT_SSH_COMMAND'], 'UserKnownHostsFile=');

            static::assertNotNull($keyFile);
            static::assertNotNull($knownHostsFile);
            static::assertFileExists($keyFile, 'Key file must exist during the callback.');
            static::assertFileExists($knownHostsFile, 'Known-hosts file must exist during the callback.');
        });

        static::assertNotNull($keyFile);
        static::assertNotNull($knownHostsFile);
        static::assertFileDoesNotExist($keyFile, 'Key file must be deleted after withSshAuth returns.');
        static::assertFileDoesNotExist($knownHostsFile, 'Known-hosts file must be deleted after withSshAuth returns.');
    }

    public function testWithSshAuthDeletesTempFilesAfterCallbackException(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:encrypted');
        $this->encryptionService->expects($this->once())->method('decrypt')->willReturn('fake-key');

        $keyFile        = null;
        $knownHostsFile = null;
        $exception      = null;

        try {
            $this->service->withSshAuth($credential, static function (array $env) use (&$keyFile, &$knownHostsFile): never {
                $keyFile        = self::extractPathFromSshCommand($env['GIT_SSH_COMMAND'], '-i');
                $knownHostsFile = self::extractPathFromSshCommand($env['GIT_SSH_COMMAND'], 'UserKnownHostsFile=');

                throw new \RuntimeException('Simulated git failure');
            });
        } catch (\RuntimeException $e) {
            $exception = $e;
        }

        static::assertSame('Simulated git failure', $exception->getMessage());
        static::assertNotNull($keyFile);
        static::assertNotNull($knownHostsFile);
        static::assertFileDoesNotExist($keyFile, 'Key file must be deleted even when the callback throws.');
        static::assertFileDoesNotExist($knownHostsFile, 'Known-hosts file must be deleted even when the callback throws.');
    }

    public function testWithSshAuthDecryptsCredentialValue(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:some_ciphertext');

        $this->encryptionService->expects($this->once())->method('decrypt')->with('v1:some_ciphertext')->willReturn('decrypted-key');

        $this->service->withSshAuth($credential, static fn(array $env): null => null);
    }

    public function testWithSshAuthWritesKnownHostsContent(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:encrypted');
        $this->encryptionService->expects($this->once())->method('decrypt')->willReturn('fake-key');

        $this->service->withSshAuth($credential, function (array $env): void {
            $hostsFile = self::extractPathFromSshCommand($env['GIT_SSH_COMMAND'], 'UserKnownHostsFile=');
            static::assertNotNull($hostsFile);
            static::assertSame(
                base64_decode($this->knownHostsBase64, strict: true),
                file_get_contents($hostsFile)
            );
        });
    }

    public function testWithSshAuthReturnsCallbackValue(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:encrypted');
        $this->encryptionService->expects($this->once())->method('decrypt')->willReturn('fake-key');

        $result = $this->service->withSshAuth($credential, static fn(array $env): string => 'callback-output');

        static::assertSame('callback-output', $result);
    }

    public function testWithSshAuthThrowsOnEmptyKnownHosts(): void
    {
        $this->encryptionService->expects($this->never())->method('decrypt');
        $service = new GitSshSetupService($this->encryptionService, base64_encode(''));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SSH_KNOWN_HOSTS_BASE64 must be a non-empty base64-encoded known-hosts string.');

        $service->withSshAuth(new RepositoryCredential(), static fn(array $env): null => null);
    }

    public function testWithSshAuthThrowsOnInvalidBase64KnownHosts(): void
    {
        $this->encryptionService->expects($this->never())->method('decrypt');
        $service = new GitSshSetupService($this->encryptionService, '!!!not-base64!!!');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SSH_KNOWN_HOSTS_BASE64');

        $service->withSshAuth(new RepositoryCredential(), static fn(array $env): null => null);
    }

    /**
     * Extract a file path from a GIT_SSH_COMMAND string by its preceding token.
     * Handles both single-quoted (Linux) and double-quoted (Windows) paths produced by escapeshellarg().
     *
     * Examples:
     *   extractPathFromSshCommand('ssh -i \'/tmp/key\' -o ...', '-i')   → '/tmp/key'
     *   extractPathFromSshCommand('ssh -i "C:\\tmp\\key" -o ...', '-i') → 'C:\tmp\key'
     */
    private static function extractPathFromSshCommand(string $command, string $token): ?string
    {
        // Match: token + whitespace (optional) + ( 'path' | "path" | bare-path )
        $pattern = '/' . preg_quote($token, '/') . '\s*(?:\'([^\']+)\'|"([^"]+)"|(\S+))/';
        if (preg_match($pattern, $command, $m) !== 1) {
            return null;
        }

        return $m[1] !== '' ? $m[1] : ($m[2] !== '' ? $m[2] : ($m[3] !== '' ? $m[3] : null));
    }
}
