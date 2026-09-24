<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\Credential;

use DR\Review\Command\Credential\EncryptCredentialsCommand;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Service\Repository\CredentialEncryptionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(EncryptCredentialsCommand::class)]
class EncryptCredentialsCommandTest extends AbstractTestCase
{
    private RepositoryCredentialRepository&MockObject $credentialRepository;
    private CredentialEncryptionService&MockObject    $encryptionService;
    private EncryptCredentialsCommand                 $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->credentialRepository = $this->createMock(RepositoryCredentialRepository::class);
        $this->encryptionService    = $this->createMock(CredentialEncryptionService::class);
        $this->command              = new EncryptCredentialsCommand($this->credentialRepository, $this->encryptionService);
    }

    public function testExecuteNoCredentials(): void
    {
        $this->credentialRepository->expects($this->exactly(2))->method('findAll')->willReturn([]);
        $this->encryptionService->expects($this->never())->method('encrypt');
        $this->credentialRepository->expects($this->never())->method('save');

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Encrypted: 0, already encrypted (skipped): 0.', $tester->getDisplay());
        self::assertStringContainsString('Verification passed', $tester->getDisplay());
    }

    public function testExecuteSkipsAlreadyEncryptedCredentials(): void
    {
        $credential = (new RepositoryCredential())->setValue('v1:alreadyencrypted');

        $this->credentialRepository->expects($this->exactly(2))->method('findAll')->willReturn([$credential]);
        $this->encryptionService->expects($this->exactly(2))->method('isEncrypted')->with('v1:alreadyencrypted')->willReturn(true);
        $this->encryptionService->expects($this->never())->method('encrypt');
        $this->credentialRepository->expects($this->never())->method('save');

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Encrypted: 0, already encrypted (skipped): 1.', $tester->getDisplay());
    }

    public function testExecuteEncryptsPlaintextCredentials(): void
    {
        $credential = (new RepositoryCredential())->setValue('username:password');

        $this->credentialRepository->expects($this->exactly(2))->method('findAll')
            ->willReturnOnConsecutiveCalls([$credential], [$credential]);

        $this->encryptionService->expects($this->exactly(2))->method('isEncrypted')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->encryptionService->expects($this->once())->method('encrypt')
            ->with('username:password')
            ->willReturn('v1:encryptedvalue');

        $this->credentialRepository->expects($this->once())->method('save')->with($credential, true);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Encrypted: 1, already encrypted (skipped): 0.', $tester->getDisplay());
        self::assertStringContainsString('Verification passed', $tester->getDisplay());
    }

    public function testExecuteFailsVerificationWhenPlaintextRemains(): void
    {
        $credential = (new RepositoryCredential())->setValue('username:password');

        $this->credentialRepository->expects($this->exactly(2))->method('findAll')
            ->willReturn([$credential]);

        // First call: not encrypted (encrypt it), second call (verification): also not encrypted
        $this->encryptionService->expects($this->exactly(2))->method('isEncrypted')
            ->willReturn(false);

        $this->encryptionService->expects($this->once())->method('encrypt')
            ->with('username:password')
            ->willReturn('v1:encryptedvalue');

        $this->credentialRepository->expects($this->once())->method('save');

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Verification failed', $tester->getDisplay());
    }
}
