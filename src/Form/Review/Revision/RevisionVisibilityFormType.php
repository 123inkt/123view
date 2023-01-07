<?php
declare(strict_types=1);

namespace DR\Review\Form\Review\Revision;

use DR\Review\Controller\App\Revision\DetachRevisionController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RevisionVisibilityFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['reviewId' => null]);
        $resolver->addAllowedTypes('reviewId', 'int');
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var int $reviewId */
        $reviewId = $options['reviewId'];
        $formId   = 'revision-visibility-form';

        $builder->setAttribute('id', $formId);
        $builder->setAction($this->urlGenerator->generate(DetachRevisionController::class, ['id' => $reviewId]));
        $builder->setMethod('POST');

        $builder->add(
            'visibilities',
            CollectionType::class,
            [
                'allow_add'     => false,
                'allow_delete'  => false,
                'entry_type'    => RevisionVisibilityType::class,
                'entry_options' => ['form_id' => $formId]
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
