<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\AddCommentController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\LineReference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;

class AddCommentReplyFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['comment' => null]);
        $resolver->addAllowedTypes('comment', [Comment::class]);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Comment $comment */
        $comment = $options['comment'];

        $builder->setAction($this->urlGenerator->generate(AddCommentController::class, ['id' => $comment->getId()]));
        $builder->setMethod('POST');
        $builder->add(
            'message',
            TextareaType::class,
            [
                'label' => false,
                'attr' => ['placeholder' => 'leave.a.reply'],
                'constraints' => new Length(max: 2000)
            ]
        );
        $builder->add('save', SubmitType::class, ['label' => 'Reply']);
    }
}
