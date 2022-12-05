<?php
declare(strict_types=1);

namespace DR\Review\Form\Rule;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Form\Filter\InExclusionFilterType;
use DR\Review\Form\Recipient\RecipientCollectionType;
use DR\Review\Form\Repository\RepositoryChoiceType;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleType extends AbstractType
{
    public function __construct(private bool $allowCustomRecipients)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, ['label' => 'name', 'required' => true]);
        $builder->add('active', CheckboxType::class, ['label' => 'active', 'required' => false]);
        $builder->add('ruleOptions', RuleOptionsType::class);
        if ($this->allowCustomRecipients) {
            $builder->add('recipients', RecipientCollectionType::class);
        }
        $builder->add('repositories', RepositoryChoiceType::class, ['label' => 'repositories']);
        $builder->add('filters', InExclusionFilterType::class);

        $builder->get('repositories')->addModelTransformer(new CollectionToArrayTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Rule::class,]);
    }
}
