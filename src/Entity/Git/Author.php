<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git;

class Author
{
    public string $name;
    public string $email;

    public function __construct(string $name, string $email)
    {
        $this->name  = $name;
        $this->email = $email;
    }
}
