<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

use Symfony\Component\Serializer\Attribute\SerializedName;

class User
{
    public int    $id;
    public string $name;
    public string $username;
    #[SerializedName('avatar_url')]
    public string $avatarUrl;
    public string $email;
}
