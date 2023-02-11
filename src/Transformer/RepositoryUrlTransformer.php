<?php
declare(strict_types=1);

namespace DR\Review\Transformer;

use DR\Review\Utility\UriUtil;
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
    public function transform(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UriInterface === false) {
            throw new TransformationFailedException('Unable to transform value');
        }

        [$username,] = UriUtil::credentials($value);

        return [
            'url'      => (string)$value->withUserInfo(null),
            'username' => (string)$username,
            'password' => ''
        ];
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform(mixed $value): ?UriInterface
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) === false) {
            throw new TransformationFailedException('Unable to transform value');
        }

        return Uri::createFromString($value['url'])->withUserInfo($value['username'], $value['password']);
    }
}
