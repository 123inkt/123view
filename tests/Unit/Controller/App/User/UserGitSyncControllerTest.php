<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\UserGitSyncController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\User\UserGitSyncViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractControllerTestCase<UserGitSyncController>
 */
#[CoversClass(UserGitSyncController::class)]
class UserGitSyncControllerTest extends AbstractControllerTestCase
{
    public function testInvokeWithToken(): void
    {
        $token = new GitAccessToken();
        $token->setGitType(RepositoryGitType::GITLAB);
        $user = new User();
        $user->getGitAccessTokens()->add($token);

        $expected = new UserGitSyncViewModel(true, true);
        $this->expectGetUser($user);

        static::assertEquals(['gitSyncModel' => $expected], ($this->controller)());
    }

    public function testInvokeWithoutToken(): void
    {
        $user = new User();

        $expected = new UserGitSyncViewModel(true, false);
        $this->expectGetUser($user);

        static::assertEquals(['gitSyncModel' => $expected], ($this->controller)());
    }

    public function getController(): AbstractController
    {
        return new UserGitSyncController(true);
    }
}
