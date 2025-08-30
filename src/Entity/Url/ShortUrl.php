<?php
declare(strict_types=1);

namespace DR\Review\Entity\Url;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Url\ShortUrlRepository;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Validator\Constraints as Constraint;

#[ORM\Entity(repositoryClass: ShortUrlRepository::class)]
#[ORM\UniqueConstraint('UK_SHORT_KEY', ['shortKey'])]
class ShortUrl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Constraint\NotBlank]
    #[Constraint\Length(min: 1, max: 50)]
    #[Constraint\Regex('/^[A-Za-z0-9_.\+\-]+$/', message: 'short_url.short_key.invalid_characters')]
    private string $shortKey;

    #[ORM\Column(type: 'string', length: 2000)]
    #[Constraint\NotBlank]
    #[Constraint\Length(max: 2000)]
    private UriInterface $originalUrl;

    #[ORM\Column]
    private int $createTimestamp;


    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getShortKey(): string
    {
        return $this->shortKey;
    }

    public function setShortKey(string $shortKey): self
    {
        $this->shortKey = $shortKey;

        return $this;
    }

    public function getOriginalUrl(): UriInterface
    {
        return $this->originalUrl;
    }

    public function setOriginalUrl(UriInterface $originalUrl): self
    {
        $this->originalUrl = $originalUrl;

        return $this;
    }

    public function getCreateTimestamp(): int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }
}
