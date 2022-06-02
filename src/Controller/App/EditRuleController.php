<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use DR\GitCommitNotification\Form\RuleType;
use DR\GitCommitNotification\ViewModel\App\EditRuleViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class EditRuleController extends AbstractController
{
    #[Route('/rules/rule', self::class)]
    #[Template('app/edit_rule.html.twig')]
    public function __invoke(): array
    {
        $model = new EditRuleViewModel();
        $model->setForm($this->createForm(RuleType::class)->createView());

        return ['editRuleModel' => $model];
    }
}
