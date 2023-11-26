<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Transformer;

use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Transformer\UserProfileRoleTransformer;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserProfileRoleTransformer::class)]
class UserProfileRoleTransformerTest extends AbstractTestCase
{
    private UserProfileRoleTransformer $transformer;

    public function setUp(): void
    {
        parent::setUp();
        $this->transformer = new UserProfileRoleTransformer();
    }

    public function testTransform(): void
    {
        static::assertNull($this->transformer->transform(null));
        static::assertSame(Roles::PROFILE_USER, $this->transformer->transform(['ROLE_USER']));
    }

    public function testReverseTransform(): void
    {
        static::assertNull($this->transformer->reverseTransform(null));
        static::assertSame(['ROLE_USER'], $this->transformer->reverseTransform(Roles::PROFILE_USER));
    }
}
