<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Rule\RulesViewModel;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bridge\Twig\Attribute\Template;
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
