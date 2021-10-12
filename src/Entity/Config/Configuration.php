<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Configuration
{
    public Repositories $repositories;

    /**
     * @SerializedName("rule")
     * @var Rule[]
     */
    private array $rules = [];

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function addRule(Rule $rule): void
    {
        $rule->config  = $this;
        $this->rules[] = $rule;
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeRule(Rule $rule): void
    {
        // method only required for deserialization
    }
}
