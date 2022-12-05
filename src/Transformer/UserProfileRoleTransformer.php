<?php
declare(strict_types=1);

namespace DR\Review\Transformer;

use DR\Review\Security\Role\Roles;
use DR\Review\Utility\Assert;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<string[], string>
 */
class UserProfileRoleTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        return $value === null ? null : Roles::rolesToProfile(Assert::isArray($value));
    }

    /**
     * @return string[]|null
     */
    public function reverseTransform(mixed $value): ?array
    {
        return $value === null ? null : Roles::profileToRoles(Assert::isString($value));
    }
}
