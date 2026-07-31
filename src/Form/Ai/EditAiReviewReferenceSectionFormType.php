<?php
declare(strict_types=1);

namespace DR\Review\Form\Ai;

use DR\Review\Controller\App\Admin\Ai\AiReviewReferenceSectionController;
use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Entity\Ai\AiReviewReferenceSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends AbstractType<array{reference: AiReviewReference, section: AiReviewReferenceSection|null}>
 */
class EditAiReviewReferenceSectionFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array{reference: AiReviewReference, section: AiReviewReferenceSection|null} $data */
        $data = $options['data'];
        $id   = $data['section']?->hasId() === true ? $data['section']->getId() : null;

        $builder->setAction(
            $this->urlGenerator->generate(AiReviewReferenceSectionController::class, ['referenceId' => $data['reference']->getId(), 'id' => $id])
        );
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('section', AiReviewReferenceSectionType::class, ['label' => false]);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
