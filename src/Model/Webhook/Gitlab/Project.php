<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Project
{
    public int    $id;
    public string $name;
    #[SerializedName('git_http_url')]
    public string $gitHttpUrl;
}
