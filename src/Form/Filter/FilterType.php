<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Filter;

use DR\GitCommitNotification\Doctrine\Type\FilterType as EntityFilterType;
use DR\GitCommitNotification\Entity\Config\Filter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $builder->add('pattern', TextType::class, ['attr' => ['maxlength' => 255, 'placeholder' => 'Pattern']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Filter::class,]);
    }
}
