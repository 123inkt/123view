<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository;

use DR\Review\Entity\Repository\Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSetDataEvent;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepositoryType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('active', CheckboxType::class, ['label' => 'active', 'required' => false]);
        $builder->add('favorite', CheckboxType::class, ['label' => 'favorite', 'required' => false]);
        $builder->add('name', TextType::class, ['label' => 'name', 'required' => true, 'attr' => ['maxlength' => 255]]);
        $builder->add('displayName', TextType::class, ['label' => 'display.name', 'required' => true, 'attr' => ['maxlength' => 255]]);
        $builder->add('mainBranchName', TextType::class, ['label' => 'main.branch', 'required' => true, 'attr' => ['maxlength' => 255]]);
        $builder->add('url', RepositoryUrlType::class, ['label' => false, 'required' => true]);
        $builder->add(
            'updateRevisionsInterval',
            IntegerType::class,
            ['label' => 'update.revisions.interval', 'required' => true, 'attr' => ['min' => 0]]
        );
        $builder->add(
            'validateRevisionsInterval',
            IntegerType::class,
            ['label' => 'validate.revisions.interval', 'required' => true, 'attr' => ['min' => 0]]
        );

        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    public function onPostSetData(PostSetDataEvent $event): void
    {
        $test = true;
    }

    public function onPreSubmit(PreSubmitEvent $event): void
    {
        $test = true;
    }

    public function onPostSubmit(PostSubmitEvent $event): void
    {
        $test = true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Repository::class,]);
    }
}
