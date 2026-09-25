<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Controller\App\Review\Comment\AddCommentReplyController;
use DR\Review\Entity\Review\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
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
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('message', CommentType::class, ['attr' => ['placeholder' => 'leave.a.reply']]);
        $builder->add('tag', CommentTagType::class);
        $builder->add('save', SubmitType::class, ['label' => 'reply']);
    }
}
