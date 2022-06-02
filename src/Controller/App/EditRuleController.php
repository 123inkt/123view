<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use DR\GitCommitNotification\Entity\Rule;
use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\Form\EditRuleFormType;
use DR\GitCommitNotification\ViewModel\App\EditRuleViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EditRuleController extends AbstractController
{
    #[Route('/rules/rule/{id<\d+>?}', self::class, methods: 'GET')]
    #[Template('app/edit_rule.html.twig')]
    #[Entity('rule')]
    public function __invoke(?Rule $rule): array
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();
        if ($rule !== null && $rule->getUser() !== $user) {
            throw new AccessDeniedException('Access denied');
        }

        $model = new EditRuleViewModel();
        $model->setForm($this->createForm(EditRuleFormType::class, ['rule' => $rule])->createView());

        return ['editRuleModel' => $model];
    }
}
