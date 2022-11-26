<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;

#[ORM\Entity(repositoryClass: ExternalLinkRepository::class)]
class ExternalLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $pattern;

    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
