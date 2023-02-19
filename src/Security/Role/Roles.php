<?php
declare(strict_types=1);

namespace DR\Review\Security\Role;

class Roles
{
    public const PROFILE_NEW    = 'new';
    public const PROFILE_USER   = 'user';
    public const PROFILE_ADMIN  = 'admin';
    public const PROFILE_BANNED = 'ban';

    public const ROLE_USER   = 'ROLE_USER';
    public const ROLE_ADMIN  = 'ROLE_ADMIN';
    public const ROLE_BANNED = 'ROLE_BANNED';

    public const PROFILE_NAMES = [
        Roles::PROFILE_NEW    => 'user.access.new',
        Roles::PROFILE_USER   => 'user.access.user',
        Roles::PROFILE_ADMIN  => 'user.access.admin',
        Roles::PROFILE_BANNED => 'user.access.banned'
    ];

    /**
     * @return string[]
     */
    public static function profileToRoles(string $profile): array
    {
        return match ($profile) {
            self::PROFILE_USER   => [self::ROLE_USER],
            self::PROFILE_ADMIN  => [self::ROLE_USER, self::ROLE_ADMIN],
            self::PROFILE_BANNED => [self::ROLE_BANNED],
            default              => [],
        };
    }

    /**
     * @param string[] $roles
     */
    public static function rolesToProfile(array $roles): string
    {
        if (in_array(self::ROLE_BANNED, $roles, true)) {
            return self::PROFILE_BANNED;
        }

        if (in_array(self::ROLE_ADMIN, $roles, true)) {
            return self::PROFILE_ADMIN;
        }
        if (in_array(self::ROLE_USER, $roles, true)) {
            return self::PROFILE_USER;
        }

        return self::PROFILE_NEW;
    }
}
