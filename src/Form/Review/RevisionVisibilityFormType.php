<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Controller\App\Revision\DetachRevisionController;
use DR\Review\Entity\Revision\Revision;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
        $resolver->setDefaults(['reviewId' => null, 'revisions' => null]);
        $resolver->addAllowedTypes('revisions', 'array');
        $resolver->addAllowedTypes('reviewId', 'int');
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Revision[] $revisions */
        $revisions = $options['revisions'];
        /** @var int $reviewId */
        $reviewId = $options['reviewId'];
        $formId   = 'revision-visibility-form';

        $builder->setAttribute('id', $formId);
        $builder->setAction($this->urlGenerator->generate(DetachRevisionController::class, ['id' => $reviewId]));
        $builder->setMethod('POST');

        foreach ($revisions as $revision) {
            $builder->add(
                'rev' . $revision->getId(),
                CheckboxType::class,
                [
                    'data'               => false,
                    'label'              => false,
                    'translation_domain' => false,
                    'required'           => false,
                    'attr'               => ['data-component--icon-checkbox-target' => 'checkbox', 'form' => $formId]
                ]
            );
        }
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
