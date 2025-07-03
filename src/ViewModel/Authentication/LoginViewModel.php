<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\Authentication;

use DR\Review\ViewModel\ViewModelInterface;
use Symfony\Component\Form\FormView;

class LoginViewModel implements ViewModelInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly FormView $form, public readonly string $azureAdUrl)
    {
    }
}
