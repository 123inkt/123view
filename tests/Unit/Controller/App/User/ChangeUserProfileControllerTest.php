<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\User;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\User\ChangeUserProfileController;
use DR\GitCommitNotification\Controller\App\User\UsersController;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Form\User\UserProfileFormType;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\User\ChangeUserProfileController
 * @covers ::__construct
 */
class ChangeUserProfileControllerTest extends AbstractControllerTestCase
{
    private UserRepository&MockObject $userRepository;

    public function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeInvalidFormShouldNotSave(): void
    {
        $user    = new User();
        $request = new Request();

        $this->expectCreateForm(UserProfileFormType::class, $user, ['user' => $user])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);
        $this->userRepository->expects(self::never())->method('save');
        $this->expectRefererRedirect(UsersController::class);

        ($this->controller)($request, $user);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeShouldSave(): void
    {
        $user    = new User();
        $request = new Request();

        $this->expectCreateForm(UserProfileFormType::class, $user, ['user' => $user])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);
        $this->userRepository->expects(self::once())->method('save')->with($user, true);
        $this->expectAddFlash('success', 'user.profile.saved.successful');
        $this->expectRefererRedirect(UsersController::class);

        ($this->controller)($request, $user);
    }

    public function getController(): AbstractController
    {
        return new ChangeUserProfileController($this->userRepository);
    }
}
