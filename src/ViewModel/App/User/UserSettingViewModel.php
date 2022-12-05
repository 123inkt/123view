<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\User;

use Symfony\Component\Form\FormView;

/**
 * @codeCoverageIgnore
 */
class UserSettingViewModel
{
    public function __construct(public readonly FormView $form)
    {
    }
}
