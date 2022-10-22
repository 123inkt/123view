<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Asset;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Asset\AssetRepository;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
class Asset
{
    public const MAX_DATA_SIZE = 1048576;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $mimeType = null;

    #[ORM\Column(type: 'string', length: 16777215)] // mediumtext
    private ?string $data = null;

    #[ORM\Column(type: 'integer')]
    private ?int $createTimestamp = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): Asset
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): Asset
    {
        $this->data = $data;

        return $this;
    }

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(?int $createTimestamp): Asset
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
