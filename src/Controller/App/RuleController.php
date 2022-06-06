<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Rule;
use DR\GitCommitNotification\Entity\RuleFactory;
use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\Form\EditRuleFormType;
use DR\GitCommitNotification\ViewModel\App\EditRuleViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class RuleController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine, private TranslatorInterface $translator, private User $user)
    {
    }

    /**
     * @return array<string, EditRuleViewModel>|RedirectResponse
     */
    #[Route('/rules/rule/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/edit_rule.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('rule')]
    public function __invoke(Request $request, ?Rule $rule): array|RedirectResponse
    {
        if ($rule !== null && $rule->getUser() !== $this->user) {
            throw new AccessDeniedException('Access denied');
        }

        $rule ??= RuleFactory::createDefault($this->user);

        $form = $this->createForm(EditRuleFormType::class, ['rule' => $rule]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editRuleModel' => (new EditRuleViewModel())->setForm($form->createView())];
        }

        $this->doctrine->getManager()->persist($rule);
        $this->doctrine->getManager()->flush();

        return $this->redirectToRoute(DashboardController::class, ['message' => $this->translator->trans('Rule successfully saved.')]);
    }
}
