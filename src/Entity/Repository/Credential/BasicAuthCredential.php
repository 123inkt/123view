<?php
declare(strict_types=1);

namespace DR\Review\Entity\Repository\Credential;

class BasicAuthCredential implements CredentialInterface
{
    public function __construct(private readonly string $username, private readonly string $password)
    {
    }

    public function __toString(): string
    {
        return base64_encode($this->username . ':' . $this->password);
    }

    public function getAuthorizationHeader(): string
    {
        return 'Basic ' . $this;
    }

    public static function fromString(string $string): self
    {
        [$username, $password] = explode(':', base64_decode($string));

        return new self($username, $password);
    }
}
