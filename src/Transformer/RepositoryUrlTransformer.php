<?php
declare(strict_types=1);

namespace DR\Review\Transformer;

use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<UriInterface|null, string[]|null>
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

        return Uri::new($value)->withUserInfo(null);
    }
}
