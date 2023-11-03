<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

class User
{
    public int    $id;
    public string $name;
    public string $email;
}
