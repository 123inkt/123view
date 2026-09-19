<?php
declare(strict_types=1);

namespace DR\Review\Command\Credential;

use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Service\Repository\CredentialEncryptionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('credential:encrypt-credentials', 'Encrypt all plaintext repository credentials at rest (idempotent)')]
class EncryptCredentialsCommand extends Command
{
    public function __construct(
        private readonly RepositoryCredentialRepository $credentialRepository,
        private readonly CredentialEncryptionService $encryptionService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $credentials = $this->credentialRepository->findAll();
        $encrypted   = 0;
        $skipped     = 0;

        foreach ($credentials as $credential) {
            if ($this->encryptionService->isEncrypted($credential->getValue())) {
                ++$skipped;
                continue;
            }

            $credential->setValue($this->encryptionService->encrypt($credential->getValue()));
            $this->credentialRepository->save($credential, true);
            ++$encrypted;
        }

        $output->writeln(sprintf('Encrypted: %d, already encrypted (skipped): %d.', $encrypted, $skipped));

        // Verify no plaintext rows remain
        $remaining = 0;
        foreach ($this->credentialRepository->findAll() as $credential) {
            if ($this->encryptionService->isEncrypted($credential->getValue()) === false) {
                ++$remaining;
            }
        }

        if ($remaining > 0) {
            $output->writeln(sprintf('<error>Verification failed: %d credential(s) are still plaintext.</error>', $remaining));

            return self::FAILURE;
        }

        $output->writeln('Verification passed: all credentials are encrypted.');

        return self::SUCCESS;
    }
}
