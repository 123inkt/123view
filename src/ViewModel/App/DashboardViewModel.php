<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App;

use DR\GitCommitNotification\Entity\Rule;

class DashboardViewModel
{
    /** @var Rule[] */
    private array $rules = [];

    private ?string $message = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

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
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }
}
