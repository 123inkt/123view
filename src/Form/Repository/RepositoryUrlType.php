<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository;

use DR\Review\Transformer\RepositoryUrlTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class RepositoryUrlType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('url', UrlType::class, ['label' => 'url', 'required' => true, 'attr' => ['maxlength' => 255]]);
        $builder->add('username', TextType::class, ['label' => 'user.name', 'required' => true, 'attr' => ['maxlength' => 50]]);
        $builder->add('password', PasswordType::class, ['label' => 'password', 'required' => false, 'attr' => ['maxlength' => 50]]);
        $builder->addModelTransformer(new RepositoryUrlTransformer());
    }
}
