<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class RepositoryGitType extends AbstractEnumType
{
    public const GITLAB = 'gitlab';
    public const GITHUB = 'github';

    public const string TYPE   = 'enum_git_type';
    protected const array VALUES = [self::GITLAB, self::GITHUB];
}
