<?php
declare(strict_types=1);

namespace DR\Review\Validator\Repository;

use Symfony\Component\Validator\Constraint;

class CredentialCompatibilityConstraint extends Constraint
{
    public string $messageSshRequiresSshKey = 'SSH repository URLs require an SSH-key credential.';
    public string $messageHttpForbidsSshKey = 'HTTP/HTTPS repository URLs cannot use an SSH-key credential.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
