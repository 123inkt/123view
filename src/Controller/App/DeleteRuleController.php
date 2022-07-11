<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteRuleController extends AbstractController
{
    public function __construct(private RuleRepository $ruleRepository, private TranslatorInterface $translator, private ?User $user)
    {
    }

    #[Route('/app/rules/rule/delete/{id<\d+>}', self::class, methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('rule')]
    public function __invoke(Rule $rule): RedirectResponse
    {
        if ($this->user === null) {
            throw new AccessDeniedException('Access denied');
        }
        if ($rule->getUser() !== $this->user) {
            throw new NotFoundHttpException('Rule not found');
        }

        $this->ruleRepository->remove($rule, true);

        $this->addFlash('success', $this->translator->trans('rule.removed.successful', ['name' => $rule->getName()]));

        return $this->redirectToRoute(RulesController::class);
    }
}
