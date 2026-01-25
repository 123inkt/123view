<?php
declare(strict_types=1);

namespace DR\Review\Form\Review\Revision;

use DR\Review\Controller\App\Revision\DetachRevisionController;
use DR\Review\Entity\Revision\Revision;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DetachRevisionsFormType extends AbstractType
{
    private const FORM_ID = 'detach-revision-form';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'reviewId'  => null,
                'revisions' => null,
                'attr'      => ['id' => self::FORM_ID]
            ]
        );
        $resolver->addAllowedTypes('revisions', 'array');
        $resolver->addAllowedTypes('reviewId', 'int');
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Revision[] $revisions */
        $revisions = $options['revisions'];
        /** @var int $reviewId */
        $reviewId = $options['reviewId'];

        $builder->setAction($this->urlGenerator->generate(DetachRevisionController::class, ['id' => $reviewId]));
        $builder->setMethod(Request::METHOD_POST);

        foreach ($revisions as $revision) {
            $builder->add(
                'rev' . $revision->getId(),
                CheckboxType::class,
                [
                    'data'               => false,
                    'label'              => false,
                    'translation_domain' => false,
                    'required'           => false,
                    'attr'               => ['data-role' => 'detach', 'form' => self::FORM_ID]
                ]
            );
        }

        $builder->add('detach', SubmitType::class, ['label' => 'detach.revisions']);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
