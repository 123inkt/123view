<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Controller\App\Review\Comment\AddCommentController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Utils\Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddCommentFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['lineReference' => null, 'review' => null,]);
        $resolver->addAllowedTypes('lineReference', ['null', LineReference::class]);
        $resolver->addAllowedTypes('review', CodeReview::class);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CodeReview $review */
        $review = $options['review'];
        /** @var LineReference $lineReference */
        $lineReference = $options['lineReference'] ?? new LineReference();

        $builder->setAction($this->urlGenerator->generate(AddCommentController::class, ['id' => $review->getId()]));
        $builder->setMethod(Request::METHOD_POST);
        $builder->add(
            'lineReference',
            HiddenType::class,
            ['data' => (string)$lineReference, 'setter' => $this->setter(...)]
        );
        $builder->add(
            'message',
            CommentType::class,
            [
                'attr_translation_parameters' => ['line' => $lineReference->line + $lineReference->offset],
                'attr'                        => ['placeholder' => 'leave.a.comment.on.line'],
            ]
        );
        $builder->add('tag', CommentTagType::class);
        $builder->add('save', SubmitType::class, ['label' => 'add.comment']);
    }

    public function setter(Comment $comment, string $value): void
    {
        $lineReference = LineReference::fromString($value);
        $comment->setLineReference($lineReference);
        $comment->setFilePath(Assert::notNull($lineReference->oldPath ?? $lineReference->newPath));
    }
}
