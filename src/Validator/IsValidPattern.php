<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Validator;

use Symfony\Component\Validator\Constraint;

class IsValidPattern extends Constraint
{
    public function getTargets(): array|string
    {
        return [self::CLASS_CONSTRAINT];
    }

    public function validatedBy(): string
    {
        return IsValidPatternValidator::class;
    }
}
