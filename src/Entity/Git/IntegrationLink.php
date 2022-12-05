<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git;

class IntegrationLink
{
    public string $image;
    public string $url;
    public string $text;

    public function __construct(string $url, string $image, string $text)
    {
        $this->url   = $url;
        $this->image = $image;
        $this->text  = $text;
    }
}
