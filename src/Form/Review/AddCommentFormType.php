<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\AddCommentController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\LineReference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;

class AddCommentFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['lineReference' => null, 'review' => null,]);
        $resolver->addAllowedTypes('lineReference', LineReference::class);
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
        $lineReference = $options['lineReference'];

        $builder->setAction($this->urlGenerator->generate(AddCommentController::class, ['id' => $review->getId()]));
        $builder->setMethod('POST');
        $builder->add('lineReference', HiddenType::class, ['data' => (string)$lineReference]);
        $builder->add(
            'comment',
            TextareaType::class,
            [
                'label' => false,
                'attr_translation_parameters' => ['line' => $lineReference->line + $lineReference->offset],
                'attr' => ['placeholder' => 'leave.a.comment.on.line'],
                'constraints' => new Length(max: 2000)
            ]
        );
        $builder->add('save', SubmitType::class, ['label' => 'Add comment']);
    }
}
