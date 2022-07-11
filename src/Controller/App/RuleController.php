<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleFactory;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Form\EditRuleFormType;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\ViewModel\App\EditRuleViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class RuleController extends AbstractController
{
    public function __construct(private RuleRepository $ruleRepository, private TranslatorInterface $translator, private ?User $user)
    {
    }

    /**
     * @return array<string, EditRuleViewModel>|RedirectResponse
     */
    #[Route('/app/rules/rule/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/edit_rule.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('rule')]
    public function __invoke(Request $request, ?Rule $rule): array|RedirectResponse
    {
        if ($this->user === null) {
            throw new AccessDeniedException('Access denied');
        }
        if ($rule !== null && $rule->getUser() !== $this->user) {
            throw new NotFoundHttpException('Rule not found');
        }
        if ($rule === null && $request->attributes->get('id') !== null) {
            throw new NotFoundHttpException('Rule not found');
        }

        $rule ??= RuleFactory::createDefault($this->user);

        $form = $this->createForm(EditRuleFormType::class, ['rule' => $rule]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editRuleModel' => (new EditRuleViewModel())->setForm($form->createView())];
        }

        $this->ruleRepository->add($rule, true);

        $this->addFlash('success', $this->translator->trans('rule.successful.saved'));

        return $this->redirectToRoute(RulesController::class);
    }
}
