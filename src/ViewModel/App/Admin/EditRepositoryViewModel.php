<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Repository\Repository;
use Symfony\Component\Form\FormView;

class EditRepositoryViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly Repository $repository, public readonly FormView $form)
    {
    }
}
