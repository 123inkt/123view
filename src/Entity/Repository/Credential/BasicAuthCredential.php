<?php
declare(strict_types=1);

namespace DR\Review\Entity\Repository\Credential;

class BasicAuthCredential implements CredentialInterface
{
    public function __construct(private ?string $username = null, private ?string $password = null)
    {
    }

    public static function fromString(string $string): self
    {
        if ($string === '') {
            return new self();
        }

        $decoded = base64_decode($string, true);
        if ($decoded === false || str_contains($decoded, ':') === false) {
            return new self();
        }

        [$username, $password] = explode(':', $decoded, 2);

        return new self($username, $password);
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAuthorizationHeader(): string
    {
        return 'Basic ' . $this;
    }

    public function __toString(): string
    {
        return base64_encode($this->username . ':' . $this->password);
    }
}
