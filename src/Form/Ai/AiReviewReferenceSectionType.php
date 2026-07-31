<?php
declare(strict_types=1);

namespace DR\Review\Form\Ai;

use DR\Review\Entity\Ai\AiReviewReferenceSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<AiReviewReferenceSection>
 */
class AiReviewReferenceSectionType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('heading', TextType::class, ['label' => 'heading', 'required' => true]);
        $builder->add(
            'content',
            TextareaType::class,
            [
                'label'       => 'content',
                'required'    => true,
                'attr'        => ['rows' => 16],
                'constraints' => [new Assert\Length(max: AiReviewReferenceSection::MAX_CONTENT_LENGTH)],
            ]
        );
        $builder->add('sortOrder', NumberType::class, ['label' => 'sort.order', 'required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AiReviewReferenceSection::class]);
    }
}
