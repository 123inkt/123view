<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Notification;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Security\Voter\RuleVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteRuleController extends AbstractController
{
    public function __construct(private RuleRepository $ruleRepository, private TranslatorInterface $translator)
    {
    }

    #[Route('/app/rules/rule/delete/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('rule')]
    public function __invoke(Rule $rule): RedirectResponse
    {
        // check rule owner
        $this->denyAccessUnlessGranted(RuleVoter::DELETE, $rule);

        $this->ruleRepository->remove($rule, true);

        $this->addFlash('success', $this->translator->trans('rule.removed.successful', ['name' => $rule->getName()]));

        return $this->redirectToRoute(RulesController::class);
    }
}
