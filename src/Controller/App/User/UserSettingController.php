<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\User;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Form\User\UserSettingFormType;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\ViewModel\App\User\UserSettingViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserSettingController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
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
            return ['settingViewModel' => new UserSettingViewModel($form->createView())];
        }

        $this->userRepository->save($user, true);

        $this->addFlash('success', 'mail.settings.save.successfully');

        return ['settingViewModel' => new UserSettingViewModel($form->createView())];
    }
}
