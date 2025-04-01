<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository;

use DR\Review\Controller\App\Admin\RepositoryController;
use DR\Review\Entity\Repository\Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends AbstractType<array{repository: Repository}>
 */
class EditRepositoryFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array{repository: Repository|null} $data */
        $data = $options['data'];

        $builder->setAction($this->urlGenerator->generate(RepositoryController::class, ['id' => $data['repository']?->getId()]));
        $builder->setMethod('POST');
        $builder->add('repository', RepositoryType::class);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
