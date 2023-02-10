<?php
declare(strict_types=1);

namespace DR\Review\Utility;

use League\Uri\Uri;

class UriUtil
{
    /**
     * @return array{0: ?string, 1: ?string}
     */
    public static function credentials(?Uri $uri): array
    {
        if ($uri === null) {
            return [null, null];
        }

        $credentials = (string)$uri->getUserInfo();
        if (preg_match('/^(.*?)(?::(.*))?$/', $credentials, $matches) !== 1) {
            return [null, null];
        }

        $username = $matches[1] ? urldecode($matches[1]) : null;
        $username = $username === '' ? null : urldecode($username);

        $password = $matches[2] ? urldecode($matches[2]) : null;
        $password = $password === '' ? null : $password;

        return [$username, $password];
    }
}
