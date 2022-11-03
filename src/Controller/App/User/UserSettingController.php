<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\User;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Form\User\UserSettingFormType;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use DR\GitCommitNotification\ViewModel\App\User\UserSettingViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @return array<string, UserSettingViewModel>
     */
    #[Route('/app/user/settings', self::class, methods: ['GET', 'POST'])]
    #[Template('app/user/user.setting.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('rule')]
    public function __invoke(Request $request): array
    {
        $user = $this->getUser();

        $form = $this->createForm(UserSettingFormType::class, ['setting' => $user->getSetting()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['settingViewModel' => new UserSettingViewModel($form->createView())];
        }

        $this->userRepository->save($user, true);

        $this->addFlash('success', $this->translator->trans('mail.settings.save.successfully'));

        return ['settingViewModel' => new UserSettingViewModel($form->createView())];
    }
}
