<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\App\User\UserMandatoryGitlabSyncController;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserMandatoryGitlabSyncController::class)]
class UserMandatoryGitlabSyncControllerTest extends AbstractTestCase
{
    public function testInvoke(): void
    {
        $controller = new UserMandatoryGitlabSyncController();
        $result     = ($controller)();
        static::assertSame([], $result);
    }
}
