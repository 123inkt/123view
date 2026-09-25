<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DuplicateRuleController extends AbstractController
{
    public function __construct(private RuleRepository $ruleRepository)
    {
    }

    #[Route('/app/rules/rule/duplicate/{id<\d+>}', self::class, methods: ['POST'])]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] Rule $rule): RedirectResponse
    {
        $ruleCopy = clone $rule;
        $ruleCopy->setActive(false);
        $ruleCopy->setName('Copy of ' . $rule->getName());

        $this->ruleRepository->save($ruleCopy, true);

        return $this->redirectToRoute(RuleController::class, ['id' => $ruleCopy->getId()]);
    }
}
