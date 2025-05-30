<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\CredentialsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CredentialsViewModelProvider::class)]
class CredentialsViewModelProviderTest extends AbstractTestCase
{
    private RepositoryCredentialRepository&MockObject $webhookRepository;
    private CredentialsViewModelProvider              $viewModelProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webhookRepository = $this->createMock(RepositoryCredentialRepository::class);
        $this->viewModelProvider = new CredentialsViewModelProvider($this->webhookRepository);
    }

    public function testGetCredentialsViewModel(): void
    {
        $webhook = new RepositoryCredential();

        $this->webhookRepository->expects($this->once())->method('findBy')->with([], ['id' => 'ASC'])->willReturn([$webhook]);

        $viewModel = $this->viewModelProvider->getCredentialsViewModel();
        static::assertSame([$webhook], $viewModel->credentials);
    }
}
