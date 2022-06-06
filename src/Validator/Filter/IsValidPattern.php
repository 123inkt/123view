<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Validator\Filter;

use Symfony\Component\Validator\Constraint;

class IsValidPattern extends Constraint
{
    public function getTargets(): array|string
    {
        return [self::CLASS_CONSTRAINT];
    }
}
