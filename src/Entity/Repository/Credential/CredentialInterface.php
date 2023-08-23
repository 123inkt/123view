<?php
declare(strict_types=1);

namespace DR\Review\Entity\Repository\Credential;

use Stringable;

interface CredentialInterface extends Stringable
{
    public function getAuthorizationHeader(): string;
}
