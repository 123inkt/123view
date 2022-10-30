<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\ViewModel\App\Rule\RulesViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class CommentMailController extends AbstractController
{
    /**
     * @return array<string, RulesViewModel>
     */
    #[Route('app/rules', name: self::class, methods: 'GET')]
    #[Template('mail//rules.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(): array
    {
        return ['rulesModel' => new RulesViewModel($this->getUser()->getRules())];
    }
}
