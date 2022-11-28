<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\Role;

use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\Role\Roles
 */
class RolesTest extends AbstractTestCase
{
    /**
     * @covers ::rolesToProfile
     */
    public function testRolesToProfile(): void
    {
        static::assertSame(Roles::PROFILE_NEW, Roles::rolesToProfile([]));
        static::assertSame(Roles::PROFILE_USER, Roles::rolesToProfile([Roles::ROLE_USER]));
        static::assertSame(Roles::PROFILE_ADMIN, Roles::rolesToProfile([Roles::ROLE_USER, Roles::ROLE_ADMIN]));
        static::assertSame(Roles::PROFILE_BANNED, Roles::rolesToProfile([Roles::ROLE_BANNED]));
    }

    /**
     * @covers ::profileToRoles
     */
    public function testProfileToRoles(): void
    {
        static::assertSame([], Roles::profileToRoles(Roles::PROFILE_NEW));
        static::assertSame([Roles::ROLE_USER], Roles::profileToRoles(Roles::PROFILE_USER));
        static::assertSame([Roles::ROLE_USER, Roles::ROLE_ADMIN], Roles::profileToRoles(Roles::PROFILE_ADMIN));
        static::assertSame([Roles::ROLE_BANNED], Roles::profileToRoles(Roles::PROFILE_BANNED));
    }
}
