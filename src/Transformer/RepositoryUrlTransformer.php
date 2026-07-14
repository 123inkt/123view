<?php
declare(strict_types=1);

namespace DR\Review\Transformer;

use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<UriInterface|null, string|null>
 */
class RepositoryUrlTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UriInterface === false) {
            throw new TransformationFailedException('Unable to transform value');
        }

        // SSH URLs must keep the username (it is part of the address, not a credential).
        // For all other schemes, strip user-info so credentials are never shown in the form.
        if ($value->getScheme() === 'ssh') {
            return (string)$value;
        }

        return (string)$value->withUserInfo(null);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform(mixed $value): ?UriInterface
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) === false) {
            throw new TransformationFailedException('Unable to transform value');
        }

        // Normalize SCP-style syntax (e.g. git@host:path/repo.git) to canonical ssh:// URI.
        // Match: optional-user@host:path  – must have no scheme prefix and no leading slash in path
        if (preg_match('/^([^@\/:]+@[^\/:]+):([^\/].*)$/', $value, $matches) === 1) {
            $value = 'ssh://' . $matches[1] . '/' . $matches[2];
        }

        $uri = Uri::new($value);

        // SSH URLs keep user-info; other schemes strip credentials from the stored URL.
        if ($uri->getScheme() === 'ssh') {
            return $uri;
        }

        return $uri->withUserInfo(null);
    }
}
