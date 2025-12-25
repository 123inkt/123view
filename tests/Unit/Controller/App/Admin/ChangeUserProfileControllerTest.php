<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\ChangeUserProfileController;
use DR\Review\Controller\App\Admin\UsersController;
use DR\Review\Entity\User\User;
use DR\Review\Form\User\UserProfileFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractControllerTestCase<ChangeUserProfileController>
 */
#[CoversClass(ChangeUserProfileController::class)]
class ChangeUserProfileControllerTest extends AbstractControllerTestCase
{
    private UserRepository&MockObject $userRepository;

    public function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        parent::setUp();
    }

    public function testInvokeInvalidFormShouldNotSave(): void
    {
        $user    = new User();
        $request = new Request();

        $this->expectCreateForm(UserProfileFormType::class, $user, ['user' => $user])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);
        $this->userRepository->expects($this->never())->method('save');
        $this->expectRefererRedirect(UsersController::class);

        ($this->controller)($request, $user);
    }

    public function testInvokeShouldSave(): void
    {
        $user    = new User();
        $request = new Request();

        $this->expectCreateForm(UserProfileFormType::class, $user, ['user' => $user])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);
        $this->userRepository->expects($this->once())->method('save')->with($user, true);
        $this->expectAddFlash('success', 'user.profile.saved.successful');
        $this->expectRefererRedirect(UsersController::class);

        ($this->controller)($request, $user);
    }

    public function getController(): AbstractController
    {
        return new ChangeUserProfileController($this->userRepository);
    }
}
