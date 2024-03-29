<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\Authentication;

use Symfony\Component\Form\FormView;

class LoginViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly FormView $form, public readonly string $azureAdUrl)
    {
    }
}
