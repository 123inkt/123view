<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Filter;

use DR\GitCommitNotification\Doctrine\Type\FilterType as EntityFilterType;
use DR\GitCommitNotification\Entity\Filter;
use DR\GitCommitNotification\Validator\IsValidPattern;
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
                'choices'  => [
                    'File'    => EntityFilterType::FILE,
                    'Subject' => EntityFilterType::SUBJECT,
                    'Author'  => EntityFilterType::AUTHOR,
                ],
                'multiple' => false,
                'expanded' => false
            ]
        );
        $builder->add('pattern', TextType::class, ['attr' => ['maxlength' => 255, 'placeholder' => 'Pattern']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'  => Filter::class,
                'constraints' => [new IsValidPattern()]
            ]
        );
    }
}
