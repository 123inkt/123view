<?php
declare(strict_types=1);

namespace DR\Review\Form\Ai;

use DR\Review\Controller\App\Admin\Ai\AiReviewConfigurationController;
use DR\Review\Entity\Ai\AiReviewConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends AbstractType<array{configuration: AiReviewConfiguration}>
 */
class EditAiReviewConfigurationFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAction($this->urlGenerator->generate(AiReviewConfigurationController::class));
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('configuration', AiReviewConfigurationType::class, ['label' => false]);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
