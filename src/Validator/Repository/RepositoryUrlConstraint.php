<?php
declare(strict_types=1);

namespace DR\Review\Validator\Repository;

use Symfony\Component\Validator\Constraint;

class RepositoryUrlConstraint extends Constraint
{
    public string $messageInvalidUrl       = 'This value is not a valid repository URL.';
    public string $messageUnsupportedScheme = 'Repository URL scheme must be http, https, or ssh.';
    public string $messageSshRequiresUser  = 'SSH repository URLs must contain an explicit username (e.g. ssh://git@host/path).';
}
