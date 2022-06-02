<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\ViewModel\App\DashboardViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('app/', name: self::class, methods: 'GET')]
    #[Template('app/dashboard.html.twig')]
    public function __invoke(Request $request): array
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        $model = new DashboardViewModel();
        $model->setMessage($request->query->has('message') ? (string)$request->query->get('message') : null);
        $model->setRules(iterator_to_array($user->getRules()));

        return ['dashboardModel' => $model];
    }
}
