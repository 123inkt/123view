<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Rule;

use Symfony\Component\Form\FormView;

class EditRuleViewModel
{
    private ?FormView $form = null;

    public function getForm(): ?FormView
    {
        return $this->form;
    }

    public function setForm(?FormView $form): static
    {
        $this->form = $form;

        return $this;
    }
}
