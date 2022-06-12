<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\ViewModel\App\RulesViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RulesController extends AbstractController
{
    public function __construct(private User $user)
    {
    }

    /**
     * @return array<string, RulesViewModel>
     */
    #[Route('app/', name: self::class, methods: 'GET')]
    #[Template('app/rules.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(Request $request): array
    {
        $model = new RulesViewModel();
        $model->setRules(iterator_to_array($this->user->getRules()));

        return ['rulesModel' => $model];
    }
}
