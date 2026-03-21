<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleFactory;
use DR\Review\Form\Rule\EditRuleFormType;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\RuleVoter;
use DR\Review\ViewModel\App\Rule\EditRuleViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RuleController extends AbstractController
{
    public function __construct(private RuleRepository $ruleRepository)
    {
    }

    /**
     * @return array<string, EditRuleViewModel>|RedirectResponse
     */
    #[Route('/app/rules/rule/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/notification/edit_rule.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] ?Rule $rule): array|RedirectResponse
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

        $this->addFlash('success', 'rule.successful.saved');

        return $this->redirectToRoute(RulesController::class);
    }
}
