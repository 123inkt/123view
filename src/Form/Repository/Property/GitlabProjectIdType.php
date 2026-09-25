<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository\Property;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Repository>
 */
class GitlabProjectIdType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label'       => 'gitlab.project.id',
                'help'        => 'gitlab.project.id.help',
                'required'    => false,
                'attr'        => ['min' => 1],
                'constraints' => new Assert\Range(min: 1),
                'getter'      => [$this, 'getProperty'],
                'setter'      => [$this, 'setProperty']
            ]
        );
    }

    public function getProperty(Repository $repository): ?int
    {
        $value = $repository->getRepositoryProperty('gitlab-project-id');

        return $value === null ? null : (int)$value;
    }

    public function setProperty(Repository $repository, ?int $value): void
    {
        if ($value === null) {
            $repository->getRepositoryProperties()->remove('gitlab-project-id');
        } else {
            $repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', (string)$value));
        }
    }

    public function getParent(): string
    {
        return IntegerType::class;
    }
}
