<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\ViewModel\App\Admin\CredentialsViewModel;

class CredentialsViewModelProvider
{
    public function __construct(private readonly RepositoryCredentialRepository $credentialRepository)
    {
    }

    public function getCredentialsViewModel(): CredentialsViewModel
    {
        $credentials = $this->credentialRepository->findBy([], ['id' => 'ASC']);

        return new CredentialsViewModel($credentials);
    }
}
