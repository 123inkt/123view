<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\Role;

use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Security\Role\Roles
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
