<?php
declare(strict_types=1);

namespace DR\Review\Form\Ai;

use DR\Review\Entity\Ai\AiReviewReferenceMatcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<AiReviewReferenceMatcher>
 */
class AiReviewReferenceMatcherType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'filePattern',
            TextType::class,
            ['label' => 'file.pattern', 'required' => true, 'attr' => ['placeholder' => 'e.g. *.php or src/Entity/**']]
        );
        $builder->add(
            'codeMarker',
            TextType::class,
            ['label' => 'code.marker', 'required' => false, 'attr' => ['placeholder' => 'e.g. extends (optional)']]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AiReviewReferenceMatcher::class]);
    }
}
