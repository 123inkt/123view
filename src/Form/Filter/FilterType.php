<?php
declare(strict_types=1);

namespace DR\Review\Form\Filter;

use DR\Review\Doctrine\Type\FilterType as EntityFilterType;
use DR\Review\Entity\Notification\Filter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Filter>
 */
class FilterType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => [
                    'filter.type.file' => EntityFilterType::FILE,
                    'filter.type.subject' => EntityFilterType::SUBJECT,
                    'filter.type.author' => EntityFilterType::AUTHOR,
                ],
                'multiple' => false,
                'expanded' => false
            ]
        );
        $builder->add('pattern', TextType::class, ['attr' => ['maxlength' => 255, 'placeholder' => 'pattern']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Filter::class]);
    }
}
