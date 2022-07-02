<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\ViewModel\App\RulesViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RulesController extends AbstractController
{
    public function __construct(private ?User $user)
    {
    }

    /**
     * @return array<string, RulesViewModel>
     */
    #[Route('app/', name: self::class, methods: 'GET')]
    #[Template('app/rules.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(): array
    {
        if ($this->user === null) {
            throw new AccessDeniedException('Access denied');
        }

        $model = new RulesViewModel();
        $model->setRules(iterator_to_array($this->user->getRules()));

        return ['rulesModel' => $model];
    }
}
