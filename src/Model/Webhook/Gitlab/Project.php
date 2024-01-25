<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

class Project
{
    public int    $id;
    public string $name;
    public string $description;
    public string $url;
}
