<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Repository\RepositoryCredential;

class CredentialsViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param RepositoryCredential[] $credentials
     */
    public function __construct(public readonly array $credentials)
    {
    }
}
