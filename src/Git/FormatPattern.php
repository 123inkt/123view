<?php
declare(strict_types=1);

namespace DR\Review\Git;

/**
 * @link https://git-scm.com/docs/pretty-formats
 */
class FormatPattern
{
    public const PARENT_HASH          = '%P';
    public const COMMIT_HASH          = '%H';
    public const TREE_HASH            = '%T';
    public const AUTHOR_NAME          = '%an';
    public const AUTHOR_EMAIL         = '%ae';
    public const AUTHOR_DATE          = '%at';
    public const AUTHOR_DATE_RELATIVE = '%ar';
    public const AUTHOR_DATE_ISO8601  = '%aI';
    public const SUBJECT              = '%s';
    public const BODY                 = '%b';
    public const REF_NAMES            = '%D';
    public const REF_NAME_SOURCE      = '%S';

    // key for defining the `patch` data.
    public const PATCH = 'patch';
}
