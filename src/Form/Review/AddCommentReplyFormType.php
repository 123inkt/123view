<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\AddCommentReplyController;
use DR\GitCommitNotification\Entity\Review\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        $builder->setAction($this->urlGenerator->generate(AddCommentReplyController::class, ['id' => $comment->getId()]));
        $builder->setMethod('POST');
        $builder->add('message', CommentType::class, ['attr' => ['placeholder' => 'leave.a.reply']]);
        $builder->add('save', SubmitType::class, ['label' => 'Reply']);
    }
}
