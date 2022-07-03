<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Rule;

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use DR\GitCommitNotification\Doctrine\Type\MailThemeType;
use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Entity\Config\RuleOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleOptionsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'frequency',
            ChoiceType::class,
            [
                'choices' => [
                    Frequency::ONCE_PER_HOUR        => Frequency::ONCE_PER_HOUR,
                    Frequency::ONCE_PER_TWO_HOURS   => Frequency::ONCE_PER_TWO_HOURS,
                    Frequency::ONCE_PER_THREE_HOURS => Frequency::ONCE_PER_THREE_HOURS,
                    Frequency::ONCE_PER_FOUR_HOURS  => Frequency::ONCE_PER_FOUR_HOURS,
                    Frequency::ONCE_PER_DAY         => Frequency::ONCE_PER_DAY,
                    Frequency::ONCE_PER_WEEK        => Frequency::ONCE_PER_WEEK,
                ]
            ]
        );
        $builder->add(
            'theme',
            ChoiceType::class,
            [
                'label'                     => 'Mail theme',
                'choices'                   => [
                    'Upsource' => MailThemeType::UPSOURCE,
                    'Darcula'  => MailThemeType::DARCULA
                ],
                'choice_translation_domain' => false,
                'multiple'                  => false,
                'expanded'                  => false,
            ]
        );
        $builder->add('subject', TextType::class, ['required' => false, 'label' => 'Mail subject']);
        $builder->add(
            'diffAlgorithm',
            ChoiceType::class,
            [
                'choices'                   => array_combine(DiffAlgorithmType::VALUES, DiffAlgorithmType::VALUES),
                'preferred_choices'         => [DiffAlgorithmType::MYERS],
                'choice_translation_domain' => false,
                'multiple'                  => false,
                'expanded'                  => false,
            ]
        );
        $builder->add('ignoreSpaceAtEol', CheckboxType::class, ['required' => false]);
        $builder->add('ignoreSpaceChange', CheckboxType::class, ['required' => false]);
        $builder->add('ignoreAllSpace', CheckboxType::class, ['required' => false]);
        $builder->add('ignoreBlankLines', CheckboxType::class, ['required' => false]);
        $builder->add('excludeMergeCommits', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RuleOptions::class,]);
    }
}
