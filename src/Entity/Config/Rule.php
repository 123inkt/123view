<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Rule
{
    public string $name;
    /** @SerializedName("@active") */
    public bool $active = true;
    /** @SerializedName("@theme") */
    public string               $theme = 'upsource';
    public RepositoryReferences $repositories;
    /** @SerializedName("@frequency") */
    public string $frequency = 'once-per-hour';
    /** @SerializedName("@diffAlgorithm") */
    public string $diffAlgorithm = 'histogram';
    /** @SerializedName("@ignoreSpaceAtEol") */
    public bool $ignoreSpaceAtEol = true;
    /** @SerializedName("@ignoreSpaceChange") */
    public bool $ignoreSpaceChange = false;
    /** @SerializedName("@ignoreAllSpace") */
    public bool $ignoreAllSpace = false;
    /** @SerializedName("@ignoreBlankLines") */
    public bool $ignoreBlankLines = false;
    /** @SerializedName("@excludeMergeCommits") */
    public bool    $excludeMergeCommits = true;
    public ?string $subject             = null;
    /** @SerializedName("external_links") */
    public ?ExternalLinks $externalLinks = null;
    public Recipients     $recipients;
    public ?Definition    $include       = null;
    public ?Definition    $exclude       = null;
    public Configuration  $config;
}
