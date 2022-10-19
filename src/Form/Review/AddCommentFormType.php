<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\AddCommentController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddCommentFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'lineReference' => null,
                'review'        => null,
            ]
        );
        $resolver->addAllowedTypes('lineReference', 'string');
        $resolver->addAllowedTypes('review', CodeReview::class);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CodeReview $review */
        $review = $options['review'];
        /** @var string $lineReference */
        $lineReference = $options['lineReference'];

        $url = $this->urlGenerator->generate(AddCommentController::class, ['id' => $review->getId(), 'lineReference' => $lineReference]);

        $lineNumber = preg_replace('/(\d*):(\d*)/', '$2', $lineReference);

        $builder->setAction($url);
        $builder->setMethod('POST');
        $builder->add('comment', TextareaType::class, ['label' => false, 'attr' => ['placeholder' => 'Leave comment on line ' . $lineNumber]]);
        $builder->add('save', SubmitType::class, ['label' => 'Add comment']);
    }
}
