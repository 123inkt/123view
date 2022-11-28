<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\User;

use DR\GitCommitNotification\Entity\User\User;
use Symfony\Component\Form\FormView;

class UsersViewModel
{
    /**
     * @param User[]     $users
     * @param FormView[] $forms
     *
     * @codeCoverageIgnore
     */
    public function __construct(public readonly array $users, public readonly array $forms)
    {
    }
}
