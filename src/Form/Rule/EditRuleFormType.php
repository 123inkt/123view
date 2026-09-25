<?php
declare(strict_types=1);

namespace DR\Review\Form\Rule;

use DR\Review\Controller\App\Notification\RuleController;
use DR\Review\Entity\Notification\Rule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditRuleFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array{rule: Rule|null} $data */
        $data = $options['data'];

        $ruleId = null;
        if (isset($data['rule']) && $data['rule']->hasId()) {
            $ruleId = $data['rule']->getId();
        }

        $builder->setAction($this->urlGenerator->generate(RuleController::class, ['id' => $ruleId]));
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('rule', RuleType::class);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
