<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

class ExternalLink
{
    /** @SerializedName("@pattern") */
    public string $pattern;
    /** @SerializedName("@url") */
    public string $url;
}
