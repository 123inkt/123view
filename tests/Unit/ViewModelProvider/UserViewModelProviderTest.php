<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\User\User;
use DR\Review\Form\User\UserProfileFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\UserViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

#[CoversClass(UserViewModelProvider::class)]
class UserViewModelProviderTest extends AbstractTestCase
{
    private FormFactoryInterface&MockObject $formFactory;
    private UserRepository&MockObject       $userRepository;
    private UserViewModelProvider           $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->formFactory    = $this->createMock(FormFactoryInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->provider       = new UserViewModelProvider($this->formFactory, $this->userRepository);
    }

    public function testGetUsersViewModel(): void
    {
        $user = new User();
        $user->setId(123);

        $this->userRepository->expects($this->once())->method('findBy')->with([], ['name' => 'ASC'])->willReturn([$user]);

        $formView = $this->createMock(FormView::class);
        $form     = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('createView')->willReturn($formView);

        $this->formFactory->expects($this->once())->method('create')->with(UserProfileFormType::class, $user, ['user' => $user])->willReturn($form);

        $viewModel = $this->provider->getUsersViewModel();
        static::assertSame([$user], $viewModel->users);
        static::assertSame([123 => $formView], $viewModel->forms);
    }

    public function testGetUsersViewModelWithSortedUsers(): void
    {
        $userA = new User();
        $userA->setId(123);
        $userA->setRoles([Roles::ROLE_BANNED]);
        $userB = new User();
        $userB->setId(123);
        $userB->setRoles([Roles::ROLE_USER]);
        $userC = new User();
        $userC->setId(123);

        $this->userRepository->expects($this->once())->method('findBy')->with([], ['name' => 'ASC'])->willReturn([$userA, $userB, $userC]);

        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn($this->createMock(FormView::class));
        $this->formFactory->method('create')->willReturn($form);

        $viewModel = $this->provider->getUsersViewModel();
        static::assertSame([$userC, $userB, $userA], $viewModel->users);
    }
}
