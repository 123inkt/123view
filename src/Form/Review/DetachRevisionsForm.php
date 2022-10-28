<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\DetachRevisionController;
use DR\GitCommitNotification\Entity\Review\Revision;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DetachRevisionsForm extends AbstractType
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

        $builder->setAction($this->urlGenerator->generate(DetachRevisionController::class, ['id' => $reviewId]));
        $builder->setMethod('POST');

        foreach ($revisions as $revision) {
            $builder->add(
                'rev' . $revision->getId(),
                CheckboxType::class,
                ['data' => false, 'label' => false, 'translation_domain' => false, 'required' => false]
            );
        }

        $builder->add('detach', SubmitType::class, ['label' => 'detach.revisions']);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
