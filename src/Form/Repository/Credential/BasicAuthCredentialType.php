<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository\Credential;

use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicAuthCredentialType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('username', TextType::class, ['label' => 'username']);
        $builder->add('password', PasswordType::class, ['label' => 'username']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => BasicAuthCredential::class,]);
    }
}
