<?php
declare(strict_types=1);

namespace DR\Review\Model\Git;

/**
 * @link https://git-scm.com/docs/pretty-formats
 */
class FormatPattern
{
    public const string PARENT_HASH          = '%P';
    public const string COMMIT_HASH          = '%H';
    public const string TREE_HASH            = '%T';
    public const string AUTHOR_NAME          = '%an';
    public const string AUTHOR_EMAIL         = '%ae';
    public const string AUTHOR_DATE          = '%at';
    public const string AUTHOR_DATE_RELATIVE = '%ar';
    public const string AUTHOR_DATE_ISO8601  = '%aI';
    public const string SUBJECT              = '%s';
    public const string BODY                 = '%b';
    public const string REF_NAMES            = '%D';
    public const string REF_NAME_SOURCE      = '%S';

    // key for defining the `patch` data.
    public const string PATCH = 'patch';
}
