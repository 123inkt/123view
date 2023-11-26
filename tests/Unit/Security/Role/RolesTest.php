<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\Role;

use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Roles::class)]
class RolesTest extends AbstractTestCase
{
    public function testRolesToProfile(): void
    {
        static::assertSame(Roles::PROFILE_NEW, Roles::rolesToProfile([]));
        static::assertSame(Roles::PROFILE_USER, Roles::rolesToProfile([Roles::ROLE_USER]));
        static::assertSame(Roles::PROFILE_ADMIN, Roles::rolesToProfile([Roles::ROLE_USER, Roles::ROLE_ADMIN]));
        static::assertSame(Roles::PROFILE_BANNED, Roles::rolesToProfile([Roles::ROLE_BANNED]));
    }

    public function testProfileToRoles(): void
    {
        static::assertSame([], Roles::profileToRoles(Roles::PROFILE_NEW));
        static::assertSame([Roles::ROLE_USER], Roles::profileToRoles(Roles::PROFILE_USER));
        static::assertSame([Roles::ROLE_USER, Roles::ROLE_ADMIN], Roles::profileToRoles(Roles::PROFILE_ADMIN));
        static::assertSame([Roles::ROLE_BANNED], Roles::profileToRoles(Roles::PROFILE_BANNED));
    }
}
