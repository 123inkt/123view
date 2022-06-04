<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form;

use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Repository\RepositoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepositoryChoiceType extends AbstractType
{
    public function __construct(private RepositoryRepository $repositoryRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $repositories = $this->repositoryRepository->findBy([], ['name' => 'ASC']);
        $resolver->setDefaults(
            [
                'choices'      => $repositories,
                'choice_value' => 'id',
                'choice_label' => static fn(?Repository $repository) => $repository?->getName() ?? '',
                'multiple'     => true,
                'expanded'     => true,
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
