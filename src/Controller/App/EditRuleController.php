<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use DR\GitCommitNotification\Form\EditRuleFormType;
use DR\GitCommitNotification\ViewModel\App\EditRuleViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class EditRuleController extends AbstractController
{
    #[Route('/rules/rule', self::class, methods: 'GET')]
    #[Template('app/edit_rule.html.twig')]
    public function __invoke(): array
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $model = new EditRuleViewModel();
        $model->setForm($this->createForm(EditRuleFormType::class)->createView());

        return ['editRuleModel' => $model];
    }
}
