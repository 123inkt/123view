<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Auth;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Controller\Auth\RegistrationController;
use DR\Review\Entity\User\User;
use DR\Review\Form\User\RegistrationFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\Auth\RegistrationController
 * @covers ::__construct
 */
class RegistrationControllerTest extends AbstractControllerTestCase
{
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private UserRepository&MockObject              $userRepository;
    private Security&MockObject                    $security;

    public function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->security       = $this->createMock(Security::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testRegisterShowForm(): void
    {
        $request = new Request();
        $view    = $this->createMock(FormView::class);

        $this->expectCreateForm(RegistrationFormType::class, new User())
            ->handleRequest($request)
            ->isSubmittedWillReturn(false)
            ->createViewWillReturn($view);

        $result = ($this->controller)($request);
        static::assertSame(['registrationForm' => $view], $result);
    }

    /**
     * @covers ::__invoke
     */
    public function testRegisterSubmitFormFirstUser(): void
    {
        $userCount = 0;
        $request   = new Request();

        $userA = new User();
        $userB = new User();
        $userB->setPassword('pass');
        $userB->setRoles([Roles::ROLE_USER, Roles::ROLE_ADMIN]);

        $this->expectCreateForm(RegistrationFormType::class, $userA)
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true)
            ->getWillReturn(['plainPassword' => 'plain']);
        $this->passwordHasher->expects(self::once())->method('hashPassword')->with($userA, 'plain')->willReturn('pass');
        $this->userRepository->expects(self::once())->method('getUserCount')->willReturn($userCount);
        $this->userRepository->expects(self::once())->method('save')->with($userB, true);
        $this->security->expects(self::once())->method('login')->with($userB, "security.authenticator.form_login.main", "main");
        $this->expectRedirectToRoute(ProjectsController::class)->willReturn('url');

        ($this->controller)($request);
    }

    /**
     * @covers ::__invoke
     */
    public function testRegisterSubmitForm(): void
    {
        $userCount = 1;
        $request   = new Request();

        $userA = new User();
        $userB = new User();
        $userB->setPassword('pass');

        $this->expectCreateForm(RegistrationFormType::class, $userA)
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true)
            ->getWillReturn(['plainPassword' => 'plain']);
        $this->passwordHasher->expects(self::once())->method('hashPassword')->with($userA, 'plain')->willReturn('pass');
        $this->userRepository->expects(self::once())->method('getUserCount')->willReturn($userCount);
        $this->userRepository->expects(self::once())->method('save')->with($userB, true);
        $this->security->expects(self::once())->method('login')->with($userB, "security.authenticator.form_login.main", "main");
        $this->expectRedirectToRoute(ProjectsController::class)->willReturn('url');

        ($this->controller)($request);
    }

    public function getController(): AbstractController
    {
        return new RegistrationController($this->passwordHasher, $this->userRepository, $this->security);
    }
}
