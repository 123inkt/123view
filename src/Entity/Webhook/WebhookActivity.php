<?php
declare(strict_types=1);

namespace DR\Review\Entity\Webhook;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Webhook\WebhookActivityRepository;

#[ORM\Entity(repositoryClass: WebhookActivityRepository::class)]
class WebhookActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 1000)]
    private ?string $request = null;

    /** @var array<string, string> */
    #[ORM\Column(type: 'json', nullable: true)]
    private array $requestHeaders = [];

    #[ORM\Column(type: 'integer')]
    private ?int $statusCode = null;

    #[ORM\Column(type: 'string', length: 5000)]
    private ?string $response = null;

    /** @var array<string, string|string[]> */
    #[ORM\Column(type: 'json', nullable: true)]
    private array $responseHeaders = [];

    #[ORM\Column(type: 'integer')]
    private ?int $createTimestamp = null;

    #[ORM\ManyToOne(targetEntity: Webhook::class, cascade: ['persist'], inversedBy: 'activities')]
    private ?Webhook $webhook = null;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setRequest(string $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * @param array<string, string> $requestHeaders
     */
    public function setRequestHeaders(array $requestHeaders): self
    {
        $this->requestHeaders = $requestHeaders;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(string $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * @param array<string, string|string[]> $responseHeaders
     */
    public function setResponseHeaders(array $responseHeaders): self
    {
        $this->responseHeaders = $responseHeaders;

        return $this;
    }

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }

    public function getWebhook(): ?Webhook
    {
        return $this->webhook;
    }

    public function setWebhook(?Webhook $webhook): WebhookActivity
    {
        $this->webhook = $webhook;

        return $this;
    }
}
