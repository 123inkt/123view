<?php
declare(strict_types=1);

namespace DR\Review\Entity\Repository\Credential;

use LogicException;

class SshKeyCredential implements CredentialInterface
{
    public function __construct(private ?string $privateKey = null)
    {
    }

    public static function fromString(string $string): self
    {
        if ($string === '') {
            return new self();
        }

        return new self($string);
    }

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function setPrivateKey(?string $privateKey): self
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    public function getAuthorizationHeader(): string
    {
        throw new LogicException('SSH key credentials do not support HTTP authorization headers.');
    }

    public function __toString(): string
    {
        return $this->privateKey ?? '';
    }
}
