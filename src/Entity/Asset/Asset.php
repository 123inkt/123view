<?php
declare(strict_types=1);

namespace DR\Review\Entity\Asset;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Asset\AssetRepository;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
class Asset
{
    public const MAX_DATA_SIZE = 2097152;
    public const ALLOWED_MIMES = [
        'image/png',
        'image/gif',
        'image/jpeg',
        'image/jpg'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $mimeType;

    #[ORM\Column(type: 'binary', length: 16777215)]
    private string $data;

    #[ORM\Column(type: 'integer')]
    private int $createTimestamp;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    private ?User $user = null;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): Asset
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): Asset
    {
        $this->data = $data;

        return $this;
    }

    public function getHash(): string
    {
        return substr(hash('sha256', $this->data), 0, 8);
    }

    public function getCreateTimestamp(): int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): Asset
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): Asset
    {
        $this->user = $user;

        return $this;
    }
}
