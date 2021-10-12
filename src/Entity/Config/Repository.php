<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Repository
{
    /** @SerializedName("@name") */
    public string $name;
    /** @SerializedName("@url") */
    public string $url;
    /** @SerializedName("@upsource-project-id") */
    public ?string $upsourceProjectId = null;
    /** @SerializedName("@gitlab-project-id") */
    public ?int $gitlabProjectId = null;
}
