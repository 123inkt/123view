<?php
declare(strict_types=1);

namespace DR\Review\Form\Ai;

use DR\Review\Controller\App\Admin\Ai\AiReviewReferenceMatcherController;
use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Entity\Ai\AiReviewReferenceMatcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends AbstractType<array{reference: AiReviewReference, matcher: AiReviewReferenceMatcher|null}>
 */
class EditAiReviewReferenceMatcherFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array{reference: AiReviewReference, matcher: AiReviewReferenceMatcher|null} $data */
        $data = $options['data'];
        $id   = $data['matcher']?->hasId() === true ? $data['matcher']->getId() : null;

        $builder->setAction(
            $this->urlGenerator->generate(AiReviewReferenceMatcherController::class, ['referenceId' => $data['reference']->getId(), 'id' => $id])
        );
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('matcher', AiReviewReferenceMatcherType::class, ['label' => false]);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
