<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Controller\App\Review\Comment\UpdateCommentReplyController;
use DR\Review\Entity\Review\CommentReply;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditCommentReplyFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['reply' => null, 'data_class' => CommentReply::class]);
        $resolver->addAllowedTypes('reply', CommentReply::class);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CommentReply $reply */
        $reply = $options['reply'];

        $builder->setAction($this->urlGenerator->generate(UpdateCommentReplyController::class, ['id' => $reply->getId()]));
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('message', CommentType::class);
        $builder->add('tag', CommentTagType::class);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
