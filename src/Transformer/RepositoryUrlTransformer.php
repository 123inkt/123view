<?php
declare(strict_types=1);

namespace DR\Review\Transformer;

use League\Uri\Uri;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<string, string[]>
 */
class RepositoryUrlTransformer implements DataTransformerInterface
{

    /**
     * @inheritDoc
     */
    public function transform(mixed $value): array
    {
        if (is_string($value) === false) {
            new TransformationFailedException('Unable to transform value');
        }

        $uri  = Uri::createFromString($value);
        $data = [
            'url'      => (string)$uri->withUserInfo(null),
            'username' => '',
            'password' => ''
        ];

        if (preg_match('/^(.*):.*$/', (string)$uri->getUserInfo(), $matches) === 1) {
            $data['username'] = $matches[1];
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform(mixed $value): string
    {
        if (is_array($value) === false) {
            new TransformationFailedException('Unable to transform value');
        }

        return (string)Uri::createFromString($value['url'])->withUserInfo($value['username'], $value['password']);
    }
}
