<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App;

use DR\GitCommitNotification\Entity\Config\Rule;

class RulesViewModel
{
    /** @var Rule[] */
    private array $rules = [];

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param Rule[] $rules
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }
}
