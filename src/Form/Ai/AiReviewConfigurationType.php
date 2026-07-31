<?php
declare(strict_types=1);

namespace DR\Review\Form\Ai;

use DR\Review\Entity\Ai\AiReviewConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<AiReviewConfiguration>
 */
class AiReviewConfigurationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'instructions',
            TextareaType::class,
            [
                'label'    => 'ai.review.instructions',
                'required' => false,
                'attr'     => ['rows' => 20],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AiReviewConfiguration::class]);
    }
}
