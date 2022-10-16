<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Rule;

use Countable;
use DR\GitCommitNotification\Entity\Config\Rule;
use IteratorAggregate;

class RulesViewModel
{
    /**
     * @param Countable|IteratorAggregate<Rule> $rules
     */
    public function __construct(private Countable|IteratorAggregate $rules)
    {
    }

    /**
     * @return Countable|IteratorAggregate<Rule>
     */
    public function getRules(): Countable|IteratorAggregate
    {
        return $this->rules;
    }
}
