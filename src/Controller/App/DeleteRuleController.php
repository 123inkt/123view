<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Rule;
use DR\GitCommitNotification\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteRuleController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine, private TranslatorInterface $translator, private User $user)
    {
    }

    #[Route('/rules/rule/delete/{id<\d+>}', self::class, methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('rule')]
    public function __invoke(Request $request, Rule $rule): RedirectResponse
    {
        if ($rule->getUser() !== $this->user) {
            throw new AccessDeniedException('Access denied');
        }

        //$this->doctrine->getManager()->remove($rule);
        //$this->doctrine->getManager()->flush();

        return $this->redirectToRoute(
            RulesController::class,
            ['message' => $this->translator->trans('rule.removed.successful', ['name' => $rule->getName()])]
        );
    }
}
