<?php
declare(strict_types=1);

namespace DR\Review\Form\Ai;

use DR\Review\Entity\Ai\AiReviewReference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<AiReviewReference>
 */
class AiReviewReferenceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, ['label' => 'name', 'required' => true]);
        $builder->add('description', TextareaType::class, ['label' => 'description', 'required' => false, 'attr' => ['rows' => 2]]);
        $builder->add('enabled', CheckboxType::class, ['label' => 'enabled', 'required' => false]);
        $builder->add('priority', NumberType::class, ['label' => 'priority', 'required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AiReviewReference::class]);
    }
}
