<?php
declare(strict_types=1);

namespace DR\Review\Form\Review\Revision;

use DR\Review\Controller\App\Revision\UpdateRevisionVisibilityController;
use DR\Review\Entity\Revision\RevisionVisibility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends AbstractType<array{visibilities: RevisionVisibility[]}>
 */
class RevisionVisibilityFormType extends AbstractType
{
    private const FORM_ID = 'revision-visibility-form';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'reviewId' => null,
                'attr'     => ['id' => self::FORM_ID]
            ]
        );
        $resolver->addAllowedTypes('reviewId', 'int');
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var int $reviewId */
        $reviewId = $options['reviewId'];

        $builder->setAction($this->urlGenerator->generate(UpdateRevisionVisibilityController::class, ['id' => $reviewId]));
        $builder->setMethod('POST');
        $builder->add('hidden', HiddenType::class, ['data' => 'hidden']);
        $builder->add(
            'visibilities',
            CollectionType::class,
            [
                'allow_add'          => true,
                'allow_delete'       => true,
                'allow_extra_fields' => true,
                'entry_type'         => RevisionVisibilityType::class,
                'entry_options'      => ['form_id' => self::FORM_ID]
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
