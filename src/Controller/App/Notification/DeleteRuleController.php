<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\RuleVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteRuleController extends AbstractController
{
    public function __construct(private RuleRepository $ruleRepository, private TranslatorInterface $translator)
    {
    }

    #[Route('/app/rules/rule/delete/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] Rule $rule): RedirectResponse
    {
        // check rule owner
        $this->denyAccessUnlessGranted(RuleVoter::DELETE, $rule);

        $this->ruleRepository->remove($rule, true);

        $this->addFlash('success', $this->translator->trans('rule.removed.successful', ['name' => $rule->getName()]));

        return $this->redirectToRoute(RulesController::class);
    }
}
