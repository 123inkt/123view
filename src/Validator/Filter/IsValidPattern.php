<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Validator\Filter;

use Symfony\Component\Validator\Constraint;

class IsValidPattern extends Constraint
{
    public const MESSAGE_EMAIL = 'Not a valid e-mail address.';
    public const MESSAGE_REGEX = 'Not a valid regular expression.';

    public function getTargets(): array|string
    {
        return [self::CLASS_CONSTRAINT];
    }
}
