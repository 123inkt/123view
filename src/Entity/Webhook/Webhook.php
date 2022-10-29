<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Webhook;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Repository\Webhook\WebhookRepository;

#[ORM\Entity(repositoryClass: WebhookRepository::class)]
class Webhook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private ?bool $enabled = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $url = null;

    #[ORM\Column(type: 'integer', options: ['default' => 3])]
    private ?int $retries = 3;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private ?bool $verifySsl = null;

    /** @var array<string, string> */
    #[ORM\Column(type: 'json', nullable: true)]
    private array $headers = [];

    /** @phpstan-var Collection<int, WebhookActivity> */
    #[ORM\OneToMany(mappedBy: 'webhook', targetEntity: WebhookActivity::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $activities;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

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

    public function getRetries(): ?int
    {
        return $this->retries;
    }

    public function setRetries(?int $retries): Webhook
    {
        $this->retries = $retries;

        return $this;
    }

    public function isVerifySsl(): ?bool
    {
        return $this->verifySsl;
    }

    public function setVerifySsl(bool $verifySsl): self
    {
        $this->verifySsl = $verifySsl;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array<string, string> $headers
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return Collection<int, WebhookActivity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    /**
     * @param Collection<int, WebhookActivity> $activities
     */
    public function setActivities(Collection $activities): self
    {
        $this->activities = $activities;

        return $this;
    }
}
