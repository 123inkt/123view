<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Form\User\UserSettingFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\User\UserSettingViewModel;
use DR\Review\ViewModelProvider\UserSettingViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSettingController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository, private readonly UserSettingViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, UserSettingViewModel>
     */
    #[Route('/app/user/settings', self::class, methods: ['GET', 'POST'])]
    #[Template('app/user/user.setting.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array
    {
        $user = $this->getUser();

        $form = $this->createForm(UserSettingFormType::class, ['setting' => $user->getSetting()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['settingViewModel' => $this->viewModelProvider->getUserSettingViewModel($form)];
        }

        $this->userRepository->save($user, true);

        $this->addFlash('success', 'settings.save.successfully');

        return ['settingViewModel' => $this->viewModelProvider->getUserSettingViewModel($form)];
    }
}
