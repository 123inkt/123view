<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Notification;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Entity\Notification\RuleFactory;
use DR\GitCommitNotification\Form\Rule\EditRuleFormType;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Security\Voter\RuleVoter;
use DR\GitCommitNotification\ViewModel\App\Rule\EditRuleViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RuleController extends AbstractController
{
    public function __construct(private RuleRepository $ruleRepository, private TranslatorInterface $translator)
    {
    }

    /**
     * @return array<string, EditRuleViewModel>|RedirectResponse
     */
    #[Route('/app/rules/rule/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/edit_rule.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('rule')]
    public function __invoke(Request $request, ?Rule $rule): array|RedirectResponse
    {
        if ($rule !== null) {
            $this->denyAccessUnlessGranted(RuleVoter::EDIT, $rule);
        } elseif ($request->attributes->get('id') !== null) {
            throw new NotFoundHttpException('Rule not found');
        }

        $rule ??= RuleFactory::createDefault($this->getUser());

        $form = $this->createForm(EditRuleFormType::class, ['rule' => $rule]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editRuleModel' => (new EditRuleViewModel())->setForm($form->createView())];
        }

        $this->ruleRepository->save($rule, true);

        $this->addFlash('success', $this->translator->trans('rule.successful.saved'));

        return $this->redirectToRoute(RulesController::class);
    }
}
