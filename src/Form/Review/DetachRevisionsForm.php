<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Entity\Review\Revision;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
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
        $builder->add('revisionList', ChoiceType::class, [
            'required'                  => false,
            'label'                     => false,
            'choice_translation_domain' => false,
            //'choice_label'              => static fn(?Revision $rev) => (string)$rev?->getCommitHash(),
            'choice_value'              => static fn(?Revision $rev) => (string)$rev?->getId(),
            'choices'                   => $revisions,
            'multiple'                  => true,
            'expanded'                  => true
        ]);

        $builder->add('detach', SubmitType::class, ['label' => 'detach.revisions']);
    }
}
