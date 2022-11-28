<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Notification;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\ViewModel\App\Rule\RulesViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class RulesController extends AbstractController
{
    /**
     * @return array<string, RulesViewModel>
     */
    #[Route('app/rules', name: self::class, methods: 'GET')]
    #[Template('app/rules.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(): array
    {
        return ['rulesModel' => new RulesViewModel($this->getUser()->getRules())];
    }
}
