<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Rule;

use DR\GitCommitNotification\Controller\App\Notification\RuleController;
use DR\GitCommitNotification\Entity\Config\Rule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
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

        $builder->setAction($this->urlGenerator->generate(RuleController::class, ['id' => $data['rule']?->getId()]));
        $builder->setMethod('POST');
        $builder->add('rule', RuleType::class);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
