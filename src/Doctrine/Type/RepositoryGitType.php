<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class RepositoryGitType extends AbstractEnumType
{
    public const GITLAB = 'gitlab';
    public const GITHUB = 'github';
    public const OTHER  = 'other';

    public const TYPE   = 'enum_git_type';
    public const VALUES = [self::GITLAB, self::GITHUB, self::OTHER];
}
