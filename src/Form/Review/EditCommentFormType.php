<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\UpdateCommentController;
use DR\GitCommitNotification\Entity\Review\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;

class EditCommentFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['comment' => null, 'data_class' => Comment::class]);
        $resolver->addAllowedTypes('comment', Comment::class);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Comment $comment */
        $comment = $options['comment'];

        $builder->setAction($this->urlGenerator->generate(UpdateCommentController::class, ['id' => $comment->getId()]));
        $builder->setMethod('POST');
        $builder->add(
            'message',
            TextareaType::class,
            ['label' => false, 'constraints' => new Length(max: 2000)]
        );
        $builder->add('save', SubmitType::class, ['label' => 'Save']);
    }
}
