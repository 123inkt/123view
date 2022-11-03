<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\User;

use Symfony\Component\Form\FormView;

class UserSettingViewModel
{
    public function __construct(public readonly FormView $form)
    {
    }
}
