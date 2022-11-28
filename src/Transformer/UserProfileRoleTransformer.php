<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Transformer;

use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Utility\Assert;
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
