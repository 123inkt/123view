<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Form\Repository\Property\GitlabProjectIdType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepositoryType extends AbstractType
{
    public function __construct(private string $gitlabApiUrl)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('active', CheckboxType::class, ['label' => 'active', 'required' => false]);
        $builder->add('favorite', CheckboxType::class, ['label' => 'favorite', 'required' => false]);
        $builder->add(
            'name',
            TextType::class,
            ['label' => 'name', 'help' => 'repository.name.help', 'required' => true, 'attr' => ['maxlength' => 255]]
        );
        $builder->add('displayName', TextType::class, ['label' => 'display.name', 'required' => true, 'attr' => ['maxlength' => 255]]);
        $builder->add('mainBranchName', TextType::class, ['label' => 'main.branch', 'required' => true, 'attr' => ['maxlength' => 255]]);
        $builder->add('url', RepositoryUrlType::class, ['label' => false, 'required' => true]);
        $builder->add(
            'updateRevisionsInterval',
            IntegerType::class,
            ['label' => 'update.revisions.interval', 'help' => 'repository.update.interval.help', 'required' => true, 'attr' => ['min' => 0]]
        );
        $builder->add(
            'validateRevisionsInterval',
            IntegerType::class,
            ['label' => 'validate.revisions.interval', 'help' => 'repository.validation.interval.help', 'required' => true, 'attr' => ['min' => 0]]
        );

        if ($this->gitlabApiUrl !== '') {
            $builder->add('gitlabProjectId', GitlabProjectIdType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Repository::class,]);
    }
}
