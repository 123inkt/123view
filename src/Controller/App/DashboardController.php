<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\ViewModel\App\DashboardViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('app/', name: self::class)]
    #[Template('app/dashboard.html.twig')]
    public function __invoke(ManagerRegistry $doctrine): array
    {
        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => 'fdekker@123inkt.nl']);
        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $model = new DashboardViewModel();
        $model->setRules(iterator_to_array($user->getRules()));

        return ['dashboardModel' => $model];
    }
}
