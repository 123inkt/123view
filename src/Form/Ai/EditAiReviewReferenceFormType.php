<?php
declare(strict_types=1);

namespace DR\Review\Form\Ai;

use DR\Review\Controller\App\Admin\Ai\AiReviewReferenceController;
use DR\Review\Entity\Ai\AiReviewReference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends AbstractType<array{reference: AiReviewReference|null}>
 */
class EditAiReviewReferenceFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array{reference: AiReviewReference|null} $data */
        $data = $options['data'];
        $id   = $data['reference']?->hasId() === true ? $data['reference']->getId() : null;

        $builder->setAction($this->urlGenerator->generate(AiReviewReferenceController::class, ['id' => $id]));
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('reference', AiReviewReferenceType::class, ['label' => false]);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
