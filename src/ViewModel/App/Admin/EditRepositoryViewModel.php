<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use Symfony\Component\Form\FormView;

class EditRepositoryViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly FormView $form)
    {
    }
}
