<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Rule;

use Countable;
use DR\Review\Entity\Notification\Rule;
use IteratorAggregate;

class RulesViewModel
{
    /**
     * @param Countable&IteratorAggregate<Rule> $rules
     */
    public function __construct(private Countable&IteratorAggregate $rules)
    {
    }

    /**
     * @return Countable&IteratorAggregate<Rule>
     */
    public function getRules(): Countable&IteratorAggregate
    {
        return $this->rules;
    }
}
