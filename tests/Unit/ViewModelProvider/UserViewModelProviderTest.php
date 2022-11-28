<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider;

use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Form\User\UserProfileFormType;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\UserViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\UserViewModelProvider
 * @covers ::__construct
 */
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

    /**
     * @covers ::getUsersViewModel
     */
    public function testGetUsersViewModel(): void
    {
        $user = new User();
        $user->setId(123);

        $this->userRepository->expects(self::once())->method('findBy')->with([], ['name' => 'ASC'])->willReturn([$user]);

        $formView = $this->createMock(FormView::class);
        $form     = $this->createMock(FormInterface::class);
        $form->expects(self::once())->method('createView')->willReturn($formView);

        $this->formFactory->expects(self::once())->method('create')->with(UserProfileFormType::class, $user, ['user' => $user])->willReturn($form);

        $viewModel = $this->provider->getUsersViewModel();
        static::assertSame([$user], $viewModel->users);
        static::assertSame([123 => $formView], $viewModel->forms);
    }
}
