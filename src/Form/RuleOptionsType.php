<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form;

use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Entity\RuleOptions;
use Symfony\Component\Form\AbstractType;
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
                'required' => true,
                'choices'  => [
                    'Once per hour'        => Frequency::ONCE_PER_HOUR,
                    'Once per two hours'   => Frequency::ONCE_PER_TWO_HOURS,
                    'Once per three hours' => Frequency::ONCE_PER_THREE_HOURS,
                    'Once per four hours'  => Frequency::ONCE_PER_FOUR_HOURS,
                    'Once per day'         => Frequency::ONCE_PER_DAY,
                    'Once per week'        => Frequency::ONCE_PER_WEEK,
                ]
            ]
        );
        $builder->add('subject', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RuleOptions::class,]);
    }
}
