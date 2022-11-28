<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Transformer;

use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Transformer\UserProfileRoleTransformer;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Transformer\UserProfileRoleTransformer
 */
class UserProfileRoleTransformerTest extends AbstractTestCase
{
    private UserProfileRoleTransformer $transformer;

    public function setUp(): void
    {
        parent::setUp();
        $this->transformer = new UserProfileRoleTransformer();
    }

    /**
     * @covers ::transform
     */
    public function testTransform(): void
    {
        static::assertNull($this->transformer->transform(null));
        static::assertSame(Roles::PROFILE_USER, $this->transformer->transform(['ROLE_USER']));
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransform(): void
    {
        static::assertNull($this->transformer->reverseTransform(null));
        static::assertSame(['ROLE_USER'], $this->transformer->reverseTransform(Roles::PROFILE_USER));
    }
}
