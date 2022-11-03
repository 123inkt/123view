<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\User;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Form\User\UserSettingFormType;
use DR\GitCommitNotification\ViewModel\App\User\UserSettingViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserSettingController extends AbstractController
{
    /**
     * @return array<string, UserSettingViewModel>
     */
    #[Route('/app/user/settings', self::class, methods: ['GET', 'POST'])]
    #[Template('app/user/user.setting.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('rule')]
    public function __invoke(Request $request): array
    {
        $form = $this->createForm(UserSettingFormType::class, ['setting' => $this->getUser()->getSetting()]);

        return ['settingViewModel' => new UserSettingViewModel($form->createView())];
    }
}
