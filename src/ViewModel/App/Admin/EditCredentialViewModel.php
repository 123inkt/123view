<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Repository\RepositoryCredential;
use Symfony\Component\Form\FormView;

class EditCredentialViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly RepositoryCredential $credential, public readonly FormView $form)
    {
    }
}
