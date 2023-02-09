<?php
declare(strict_types=1);

namespace DR\Review\Transformer;

use DR\Review\Utility\UriUtil;
use League\Uri\Uri;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<Uri|null, string[]|null>
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

        if ($value instanceof Uri === false) {
            new TransformationFailedException('Unable to transform value');
        }

        [$username,] = UriUtil::credentials($value);

        return [
            'url'      => (string)$value->withUserInfo(null),
            'username' => $username,
            'password' => ''
        ];
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform(mixed $value): ?Uri
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) === false) {
            new TransformationFailedException('Unable to transform value');
        }

        return Uri::createFromString($value['url'])->withUserInfo($value['username'], $value['password']);
    }
}
