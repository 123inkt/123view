<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Recipient
{
    /** @SerializedName("@name") */
    public ?string $name = null;

    /** @SerializedName("@email") */
    public string $email;
}
