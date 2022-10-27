<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Entity\Review\Revision;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetachRevisionsForm extends AbstractType
{
    //public function __construct(private UrlGeneratorInterface $urlGenerator)
    //{
    //}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['revisions' => null]);
        $resolver->addAllowedTypes('revisions', 'array');
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Revision[] $revisions */
        $revisions = $options['revisions'] ?? [];

        //$builder->setAction($this->urlGenerator->generate(UpdateCommentReplyController::class, ['id' => $reply->getId()]));
        $builder->setMethod('POST');

        foreach ($revisions as $revision) {
            $builder->add(
                'rev' . $revision->getId(),
                CheckboxType::class,
                ['data' => true, 'label' => false, 'translation_domain' => false]
            );
        }

        $builder->add('detach', SubmitType::class, ['label' => 'detach.revisions']);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
