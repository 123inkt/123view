<?php
declare(strict_types=1);

namespace DR\Review\Entity\Repository;

use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use League\Uri\Contracts\UriInterface;

class RepositoryUtil
{
    public static function getUriWithCredentials(Repository $repository): UriInterface
    {
        $uri        = $repository->getUrl();
        $credential = $repository->getCredential()?->getCredentials();
        if ($credential instanceof BasicAuthCredential) {
            $uri = $uri->withUserInfo($credential->getUsername(), $credential->getPassword());
        }

        return $uri;
    }
}
