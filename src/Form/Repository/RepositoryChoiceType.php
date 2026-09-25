<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Config\RepositoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Repository>
 */
class RepositoryChoiceType extends AbstractType
{
    public function __construct(private RepositoryRepository $repositoryRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $repositories = $this->repositoryRepository->findBy(['active' => 1], ['name' => 'ASC']);
        $resolver->setDefaults(
            [
                'label'                     => 'repository',
                'choices'                   => $repositories,
                'choice_value'              => 'id',
                'choice_label'              => static fn(?Repository $repository) => $repository?->getName() ?? '',
                'choice_translation_domain' => false,
                'multiple'                  => true,
                'expanded'                  => true,
                'constraints'               => [new Assert\Count(min: 1, minMessage: 'At least {{ limit }} repository is required')]
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
