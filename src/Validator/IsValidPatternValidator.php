<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidPatternValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint): bool
    {
        return false;
    }
}
